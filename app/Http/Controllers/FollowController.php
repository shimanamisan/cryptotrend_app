<?php

namespace App\Http\Controllers;

use App\Twuser;
use App\User;
use App\Follow;
use Carbon\Carbon;
use App\TwitterUser;
use Illuminate\Http\Request;
use App\SystemManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;

class FollowController extends Controller
{
    // 自動フォローのステータス
    const AUTO_FOLLOW_STATUS_RUN = 1;
    const AUTO_FOLLOW_STATUS_STOP = 0;
    // 1日のフォロー上限の400を超えないように制御する（400に到達しないように、手前の値を設定）
    const DAY_FOLLOW_LIMIT = 395;
    // 1日にアプリ全体としてのフォロー制限を超えないように制御する
    const SYSTEM_FOLLOW_LIMIT = 990;
    // 個別フォロー/フォロー解除時に、1日20人を上回らないように判定する
    const DAY_PERSON_LIMIT = 30;
    // 15人/15分を超えないようにする為の定数
    const FOLLOW_QUARTER_LIMIT = 14;

    /*********************************************************
    * フォローボタンからユーザーをフォローする
    *********************************************************/
    public function follow(Request $request)
    {
        /************************************************
        システムとしてのリクエスト制限に関するデータ
        *************************************************/
        $SystemManager = SystemManager::where('id', 1)->first();
        // アプリ全体としてのフォロー制限のカウント数
        $one_day_system_counter = $SystemManager->one_day_system_counter;
        // アプリ全体としてフォロー制限に関する時刻
        $system_follow_release_time = $SystemManager->one_day_system_follow_release_time;

        /************************************************
        ユーザー単位としてのリクエスト制限に関するデータ
        *************************************************/
        $user = Auth::user();
        // 1日のフォロー上限を超えないように、現在のフォローした数を取得（上限395/日）
        $day_follow_limit_count = $user->day_follow_limit_count;
        // ユーザー個別の1日のフォローリクエスト制限解除時刻
        $day_follow_release_time = $user->day_follow_release_time;

        /******************************************************************
        15フォロー/15分を超えないようにするためのリクエスト制限に関するデータ
        *******************************************************************/
        // リクエスト制限解除時刻を格納
        $day_follow_quarter_release_time = $user->day_follow_quarter_release_time;
        // 15フォロー/15分のリクエスト制限の上限をカウントする
        $day_follow_quarter_limit_count = $user->day_follow_quarter_limit_count;

        /******************************************************************
        フォローボタンよりフォローする際の、リクエスト制限に関するデータ
        *******************************************************************/
        // ユーザー個別フォローリクエスト制限の上限をカウントする（上限30/日）
        $person_follow_limit_count = $user->person_follow_limit_count;
        // ユーザー個別フォローリクエスト制限の上限をカウントする（上限30/日）
        $person_follow_release_time = $user->person_follow_release_time;

        // フォローするユーザーのIDを格納
        $follow_target_id = $request->id;
        // 現在の時間を格納
        $now_time = Carbon::now();

        /************************************************
        システムとしてのリクエスト制限に関する処理
        *************************************************/
        // フォロー実行時、24時間後のリクエスト制限解除時刻をDBに登録する
        // リクエスト制限に掛からなくても、この時刻を経過すればカウントがリセットされるようにする
        if ($system_follow_release_time === null) {
            $SystemManager->one_day_system_follow_release_time = Carbon::now()->addHours(24);
            $SystemManager->update();
            $system_follow_release_time = $SystemManager->one_day_system_follow_release_time;
            Log::debug('アプリ単位での24時間後のリクエスト制限解除時刻です。');
            Log::debug('    ');
        } elseif ($system_follow_release_time !== null) {
            // 既に解除用の時刻が格納されていれば何もしない
            Log::debug('アプリ単位でのリクエスト制限解除時刻は既に格納されています。');
            Log::debug('    ');
        }
        // アプリ単位のリクエスト制限解除時刻より、現在の時刻のほうが進んでいたら制限を解除する
        if ($now_time > $system_follow_release_time) {
            $SystemManager->one_day_system_follow_release_time = Carbon::now()->addHours(24);
            $SystemManager->one_day_system_counter = 0;
            $SystemManager->update();
            $one_day_system_counter = $SystemManager->one_day_system_counter;
            Log::debug('24時間経過しました。新たに制限解除用の時刻を格納し、カウンターをリセットします。');
            Log::debug('    ');
        }
        // アプリとしての1日のフォローリクエスト制限以内か判定
        if ($one_day_system_counter >= self::SYSTEM_FOLLOW_LIMIT) {
            Log::debug('アプリ単位でのリクエスト制限を超えました 1000/日。処理を停止します。');
            Log::debug('    ');
            return response()->json(['error' => '本日のリクエスト上限に到達しました。'], 403);
        }
    
        /************************************************
        ユーザー単位としてのリクエスト制限に関する処理
        *************************************************/
        // フォロー実行時、24時間後のリクエスト制限解除時刻をDBに登録する
        // リクエスト制限に掛からなくても、この時刻を経過すればカウントがリセットされるようにする
        if ($day_follow_release_time === null) {
            $user->day_follow_release_time = Carbon::now()->addHours(24);
            $user->update();
            $day_follow_release_time = $user->day_follow_release_time;
            Log::debug('ユーザー単位での24時間後のリクエスト制限解除時刻です');
            Log::debug('    ');
        } elseif ($day_follow_release_time !== null) {
            // 既に解除用の時刻が格納されていれば何もしない
            Log::debug('ユーザー単位でのリクエスト制限解除時刻は既に格納されています。');
            Log::debug('    ');
        }
        // ユーザー個別のリクエスト制限解除時刻より、現在の時刻のほうが進んでいたら制限を解除する
        if ($now_time > $day_follow_release_time) {
            $user->day_follow_release_time = Carbon::now()->addHours(24);
            $user->day_follow_limit_count = 0;
            $user->update();
            $day_follow_limit_count = $user->day_follow_limit_count;
            Log::debug('24時間経過しました。新たに制限解除用の時刻を格納し、カウンターをリセットします。');
            Log::debug('    ');
        }
        // ユーザー単位の1日のフォローリクエスト制限以内か判定
        if ($day_follow_limit_count >= self::DAY_FOLLOW_LIMIT) {
            Log::debug('ユーザー単位のリクエスト制限を超えました 400/日。処理を停止します。');
            Log::debug('    ');
            return response()->json(['error' => '本日のリクエスト上限に到達しました。'], 403);
        }

        /************************************************
        個別フォロー（30フォロー/日）処理時のリクエスト制限に関する処理
        *************************************************/
        // フォロー実行時、24時間後のリクエスト制限解除時刻をDBに登録する
        // リクエスト制限に掛からなくても、この時刻を経過すればカウントがリセットされるようにする
        if ($person_follow_release_time === null) {
            $user->person_follow_release_time = Carbon::now()->addHours(24);
            $user->update();
            $person_follow_release_time = $user->person_follow_release_time;
            Log::debug('個別フォロー（30フォロー/日）での24時間後のリクエスト制限解除時刻です。');
            Log::debug('    ');
        } elseif ($person_follow_release_time !== null) {
            // 既に解除用の時刻が格納されていれば何もしない
            Log::debug('個別フォロー（30フォロー/日）でのリクエスト制限解除時刻は既に格納されています。');
            Log::debug('    ');
        }
        // 個別フォロー処理時のリクエスト制限解除時刻より、現在の時刻のほうが進んでいたら制限を解除する
        if ($now_time > $person_follow_release_time) {
            $user->person_follow_release_time = Carbon::now()->addHours(24);
            $user->person_follow_limit_count = 0;
            $user->update();
            $person_follow_limit_count = $user->person_follow_limit_count;
            Log::debug('24時間経過しました。新たに制限解除用の時刻を格納し、カウンターをリセットします。');
            Log::debug('    ');
        }
        // フォローボタンからの、フォローリクエスト制限以内か判定
        if ($person_follow_limit_count >= self::DAY_PERSON_LIMIT) {
            Log::debug('フォローボタンからのリクエスト制限を超えました 30/日。処理を停止します。');
            Log::debug('    ');
            return response()->json(['error' => '本日のリクエスト上限に到達しました。'], 403);
        }

        /************************************************
        15/15分リクエスト制限に関する処理
        *************************************************/
        if ($day_follow_quarter_release_time === null) {
            $user->day_follow_quarter_release_time = Carbon::now()->addHours(24);
            $user->update();
            $day_follow_quarter_release_time = $user->day_follow_quarter_release_time;
            Log::debug('15/15分リクエスト制限解除時刻です。');
            Log::debug('    ');
        } elseif ($day_follow_quarter_release_time !== null) {
            // 既に解除用の時刻が格納されていれば何もしない
            Log::debug('15/15分リクエスト制限解除時刻は既に格納されています。');
            Log::debug('    ');
        }
        // 個別フォロー処理時のリクエスト制限解除時刻より、現在の時刻のほうが進んでいたら制限を解除する
        if ($now_time > $day_follow_quarter_release_time) {
            $user->day_follow_quarter_release_time = new Carbon('+15 minutes');
            $user->day_follow_quarter_limit_count = 0;
            $user->update();
            $day_follow_quarter_limit_count = $user->day_follow_quarter_limit_count;
            Log::debug('15分経過しました。新たに制限解除用の時刻を格納し、カウンターをリセットします。');
            Log::debug('    ');
        }
        // 15/15分制限以内か判定
        if ($day_follow_quarter_limit_count >= self::FOLLOW_QUARTER_LIMIT) {
            Log::debug('15/15分リクエスト制限を超えました。処理を停止します。');
            Log::debug('    ');
            return response()->json(['error' => 'フォロー制限中です。'], 403);
        }
        // インスタンスを生成
        $connection = $this->singleFollowAuth();
        Log::debug('インスタンスを生成します。');
        Log::debug('   ');
        Log::debug('本日のリクエスト制限内です。処理を実行します。');
        Log::debug('    ');
            
        // APIのエンドポイントを叩きフォローする
        $result = $connection->post('friendships/create', [
                        'user_id' => $follow_target_id,
                        ]);

        // 通信成功時、リクエスト制限が掛かっていない場合
        if ($connection->getLastHttpCode() == 200) {
                         
            // 既にフォロー済みのユーザーはfollowsテーブルで管理しているが、既にフォロー済みだった場合の処理を記述
            if ($result->following) {
                Log::debug('既にフォローしています；'. print_r($result, true));
                // APIへのリクエストは通っているので、リミット数をカウントする。
                ++$day_follow_quarter_limit_count; // 15/15分フォロー制限用のカウント
                ++$person_follow_limit_count; // 1日30フォロー制限用のカウント
                ++$day_follow_limit_count; // 1日395フォロー制限用のカウント
                ++$one_day_system_counter; // 1日1000フォロー制限用のカウント（アプリ全体）
                $user->day_follow_quarter_limit_count = $day_follow_quarter_limit_count;
                $user->day_follow_limit_count = $day_follow_limit_count;
                $user->person_follow_limit_count = $person_follow_limit_count;
                $user->update();
                $SystemManager->one_day_system_counter = $one_day_system_counter;
                $SystemManager->update();
                return response()->json(['forbidden' => '既にフォローしているユーザーです'], 403);
            }
            // APIへのリクエスト後、リミット数をカウント
            ++$day_follow_quarter_limit_count; // 15/15分フォロー制限用のカウント
            ++$person_follow_limit_count; // 1日30フォロー制限用のカウント
            ++$day_follow_limit_count; // 1日395フォロー制限用のカウント
            ++$one_day_system_counter; // 1日1000フォロー制限用のカウント（アプリ全体）
            $user->day_follow_quarter_limit_count = $day_follow_quarter_limit_count;
            $user->day_follow_limit_count = $day_follow_limit_count;
            $user->person_follow_limit_count = $person_follow_limit_count;
            $user->update();
            $SystemManager->one_day_system_counter = $one_day_system_counter;
            $SystemManager->update();
    
            // followsテーブルへ登録
            $this->addFollowTable(Auth::user()->id, $follow_target_id);
                
            return response()->json(['success' => 'フォローしました！'], 200);
        } elseif ($connection->getLastHttpCode() == 403) {
            \Log::debug('ステータスコード403の処理です');
            \Log::debug('エラー内容を取得します '. print_r($result, true));
            \Log::debug('   ');

            // フォロー済みのユーザーだった場合は403のステータスコードを返す
            // ただしTwitterAPIの仕様では、パフォーマンスの観点からフォロー済みでもステータスコード200を返すこともある
            // なので $result->following の条件の中でも判定を行う。
            return response()->json(['error' => 'フォローに失敗しました。'], 403);
        } else {
            // 通信失敗時の処理
            return response()->json(['error' => '時間を置いてから再度実行して下さい'], 500);
        }
    }

    /*****************************
    * フォローを解除する
    ******************************/
    public function unfollow(Request $request)
    {
        /**************************************************
         * フォロー解除は1日に30アカウントまでに制限する
         **************************************************/

        $user = Auth::user();
        // 現在の時間を格納
        $now_time = Carbon::now();
        // フォロー解除するユーザーのIDを格納
        $unfollow_target_id = $request->id;

        /******************************************************************
        フォローボタンよりアンフォローする際の、リクエスト制限に関するデータ
        *******************************************************************/
        // ユーザー個別アンフォローリクエスト制限の上限をカウントする（上限30/日）
        $unfollow_limit_release_time = $user->unfollow_limit_release_time;
        // ユーザー個別アンフォローリクエスト制限の上限をカウントする（上限30/日）
        $unfollow_limit_count = $user->unfollow_limit_count;
        
        /**************************************************************************
        個別フォロー解除（30アンフォロー/日）処理時のリクエスト制限に関する処理
        ***************************************************************************/
        if ($unfollow_limit_release_time === null) {
            // アンフォロー実行時、24時間後のリクエスト制限解除時刻をDBに登録する
            // リクエスト制限に掛からなくても、この時刻を経過すればカウントがリセットされるようにする
            $user->unfollow_limit_release_time = Carbon::now()->addHours(24);
            $user->update();
            $unfollow_limit_release_time = $user->unfollow_limit_release_time;
            Log::debug('個別アンフォロー（30アンフォロー/日）での24時間後のリクエスト制限解除時刻です。');
            Log::debug('    ');
        } elseif ($unfollow_limit_release_time !== null) {
            // 既に解除用の時刻が格納されていれば何もしない
            Log::debug('個別アンフォロー（30アンフォロー/日）でのリクエスト制限解除時刻は既に格納されています。');
            Log::debug('    ');
        }
        // 個別アンフォロー処理時のリクエスト制限解除時刻より、現在の時刻のほうが進んでいたら制限を解除する
        if ($now_time > $unfollow_limit_release_time) {
            $user->unfollow_limit_release_time = Carbon::now()->addHours(24); // 解除後、新しい解除用の時刻を格納
            $user->unfollow_limit_count = 0;
            $user->update();
            $unfollow_limit_count = $user->unfollow_limit_count;
            Log::debug('24時間経過しました。新たに制限解除用の時刻を格納し、カウンターをリセットします。');
            Log::debug('    ');
        }
        // アンフォローボタンからの、フォロー解除リクエスト制限以内か判定
        if ($unfollow_limit_count >= self::DAY_PERSON_LIMIT) {
            Log::debug('アンフォローボタンからのリクエスト制限を超えました 30/日。処理を停止します。');
            Log::debug('    ');
            return response()->json(['error' => '本日のリクエスト上限に到達しました。'], 403);
        }
        // インスタンスを生成
        $connection = $this->singleFollowAuth();
        Log::debug('本日のリクエスト制限内です。処理を実行します。');
        Log::debug('    ');
        // APIのエンドポイントを叩きフォロー解除する
        $result = $connection->post('friendships/destroy', [
                    'user_id' => $unfollow_target_id,
                    ]);
        // Errorハンドリング
        // 通信成功時の処理
        if ($connection->getLastHttpCode() == 200) {
            ++$unfollow_limit_count; // 1日30アンフォロー制限用のカウント
            $user->unfollow_limit_count = $unfollow_limit_count;
            $user->update();
            // followsテーブルから削除
            $this->deleteFollowTable(Auth::user()->id, $unfollow_target_id);
            
            return response()->json(['success' => 'フォロー解除しました。'], 200);
        } else {
            // 通信失敗時の処理
            return response()->json(['error' => '問題が発生しました。時間を置いてから再度実行して下さい'], 500);
        }
    }

    // 自動フォローのON/OFFを切り替える
    public function autoFollowFlg(Request $request)
    {
        $user = Auth::user();
        // 自動フォローがONになっているか確認
        $autoFollow_flg = $request->status;
        // 自動フォローのステータスを切り替える
        if ($autoFollow_flg === 0) {
            $user->autofollow_status = ++$autoFollow_flg;
            $user->update();
            \Log::debug('自動フォローをONにしました：' . $autoFollow_flg);
            return;
        } else {
            $user->autofollow_status = --$autoFollow_flg;
            $user->update();
            \Log::debug('自動フォローをOFFにしました：' . $autoFollow_flg);
            return;
        }
    }
 
    // フォローボタンをクリックした時に使用するインスタンスを生成する
    public function singleFollowAuth()
    {
        // ヘルパー関数のconfigメソッドを通じて、config/services.phpのtwitterの登録した中身を参照
        $config = config('services.twitter');
        // APIキーを格納
        $api_key = $config['client_id'];
        $api_key_secret = $config['client_secret'];
        // アクセストークンを格納
        $access_token = session('access_token');
        $access_token_secret = session('access_token_secret');
 
        $OAuth = new TwitterOAuth($api_key, $api_key_secret, $access_token, $access_token_secret);
 
        return $OAuth;
    }

    // followsテーブルへの登録
    public function addFollowTable($userID, $twUserID)
    {
        try {
            Follow::create([
                'user_id' => $userID,
                'twuser_id' => $twUserID
            ]);

            \Log::debug('フォローしたユーザーをfollowsテーブルへ登録しました： ID'. $twUserID);
            \Log::debug('   ');
        } catch (Exception $e) {
            \Log::debug('例外が発生しました' . $e->getMessage());
            return response()->json(['error' => '問題が発生しました。しばらくお待ち下さい。'], 500);
        }
    }

    // followsテーブルから削除
    public function deleteFollowTable($userID, $twUserID)
    {
        try {
            Twuser::where('id', $twUserID)->with('follows')
                    ->first()->follows()
                    ->where('user_id', $userID)
                    ->where('delete_flg', 0)
                    ->delete();

            \Log::debug('フォローしたユーザーをfollowsテーブルから削除しました。');
            \Log::debug('   ');
        } catch (Exception $e) {
            \Log::debug('例外が発生しました' . $e->getMessage());
            return response()->json(['error' => '問題が発生しました。しばらくお待ち下さい。'], 500);
        }
    }
}
