<?php

namespace App\Http\Controllers;

use App\Twuser; // ★追記
use App\User; // ★追記
use App\Follow; // ★追記
use Carbon\Carbon; //★追記
use App\TwitterUser; // ★追記
use Illuminate\Http\Request;
use App\SystemManager; // ★追記
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // ★追記
use Abraham\TwitterOAuth\TwitterOAuth; // ★追記

class FollowController extends Controller
{
    // 自動フォローのステータス
    const AUTO_FOLLOW_STATUS_RUN = 1;
    const AUTO_FOLLOW_STATUS_STOP = 0;
    // 1日のフォロー上限の400を超えないように制御する（400に到達しないように、手前の値を設定）
    const DAY_FOLLOW_LIMIT = 395;
    // 1日にアプリ全体としてのフォロー制限を超えないように制御する
    const SYSTEM_FOLLOW_LIMIT = 990;
    // 15人/15分を超えないようにする為の定数
    const FOLLOW_LIMIT = 15;
    // 個別フォロー/フォロー解除時に、1日20人を上回らないように判定する
    const DAY_PERSON_LIMIT = 20;

    /*********************************************************
    * フォローボタンからユーザーをフォローする
    *********************************************************/
    public function follow(Request $request)
    {
        /**
        * システムとしての1日のフォロー上限回数を超えた時の処理を追記する
        */
        $SystemManager = SystemManager::where('id', 1)->first();
        $one_day_system_counter_limit = $SystemManager->one_day_system_counter;

        // 1日のフォロー上限を超えないように、現在のフォローした数を取得
        $follow_limit_count = Auth::user()->follow_limit_count;
    
        // 個別フォロー時の上限をカウントする変数
        $person_follow_limit_count = Auth::user()->person_follow_limit_count;
        
        // インスタンスを生成
        $connection = $this->singleFollowAuth();

        // フォローするユーザーのIDを格納
        $follow_target_id = $request->id;
        // 前回の15分毎のフォロー上限から15分後の値を格納（DBから引っ張ってきた値は数値ではなく数字なので変換する）
        $old_time = Auth::user()->follow_limit_time;
        // 現在の時間を格納
        $now_time = Carbon::now();
        Log::debug('現在の時刻です：'.$now_time . '  制限の時間です：'.$old_time);
        Log::debug('    ' .$person_follow_limit_count);

        // ユーザー当たりの本日のリクエスト上限の範囲内(個別フォローは20回/1日、自動フォローでは395回/1日)か判定
        // システムとしての1日のフォロー上限の範囲内か判定
        // 超えていたらフォローの処理を行わずメッセージを返す
        if ($person_follow_limit_count < self::DAY_PERSON_LIMIT &&
            $follow_limit_count < self::DAY_FOLLOW_LIMIT &&
            $one_day_system_counter_limit < self::SYSTEM_FOLLOW_LIMIT) {
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
                    ++$follow_limit_count;
                    ++$person_follow_limit_count;
                    ++$one_day_system_counter_limit;
                    Auth::user()->follow_limit_count = $follow_limit_count;
                    Auth::user()->person_follow_limit_count = $person_follow_limit_count;
                    Auth::user()->update();
                    $SystemManager->one_day_system_counter = $one_day_system_counter_limit;
                    $SystemManager->update();
                    return response()->json(['forbidden' => '既にフォローしているユーザーです'], 403);
                }
                // APIへのリクエスト後、リミット数をカウント
                ++$follow_limit_count;
                ++$person_follow_limit_count;
                ++$one_day_system_counter_limit;
                Auth::user()->follow_limit_time = Carbon::now(); // 現在の時間を格納
                Auth::user()->follow_limit_count = $follow_limit_count;
                Auth::user()->person_follow_limit_count = $person_follow_limit_count;
                Auth::user()->update();
                $SystemManager->one_day_system_counter = $one_day_system_counter_limit;
                $SystemManager->update();

                // followsテーブルへ登録
                $this->addFollowTable(Auth::user()->id, $follow_target_id);
            
                return response()->json(['success' => 'フォローしました！'], 200);
            } elseif ($connection->getLastHttpCode() == 403) {
                \Log::debug('ステータスコード403の処理です');
                // フォロー済みのユーザーだった場合は403のステータスコードを返す
                // ただしTwitterAPIの仕様では、パフォーマンスの観点からフォロー済みでもステータスコード200を返すこともある
                // なので $result->following の条件の中でも
                return response()->json(['forbidden' => '既にフォローしているユーザーです'], 403);
            } else {
                // 通信失敗時の処理
                return response()->json(['error' => '時間を置いてから再度実行して下さい'], 500);
            }
        } else {
            Log::debug('本日の個別フォローの上限に到達しました。'. $person_follow_limit_count. '/20');
            Log::debug('本日の単位ユーザーあたりのフォロー上限に到達しました。'. $follow_limit_count. '/395');
            Log::debug('    ');
            return response()->json(['error' => '本日のリクエスト上限に到達しました。'], 403);
        }
    }

    /*****************************
    * フォローを解除する
    ******************************/
    public function unfollow(Request $request)
    {
        /**
         * フォロー解除は1日に20アカウントまでに制限する
         */
        
        // インスタンスを生成
        $connection = $this->singleFollowAuth();

        // フォロー解除するユーザーのIDを格納
        $unfollow_target_id = $request->id;

        // 1日のフォロー上限を超えないように、現在のフォローした数を取得
        $unfollow_limit_count = Auth::user()->unfollow_limit_count;

        // 前回の15分毎のフォロー上限から15分後の値を格納（DBから引っ張ってきた値は数値ではなく数字なので変換する）
        $old_time = Auth::user()->unfollow_limit_time;
        // 現在の時間を格納
        $now_time = Carbon::now();
        Log::debug('現在の時刻です：'.$now_time . '  制限の時間です：'.$old_time);
        Log::debug('    ');
        // dd(' アンフォローのカウント' . $unfollow_limit_count .'    定数の値'. self::DAY_PERSON_LIMIT);
        // ユーザー当たりの本日のリクエスト上限の範囲内か判定、超えていたらフォローの処理を行わずメッセージを返す
        if ($unfollow_limit_count < self::DAY_PERSON_LIMIT) {
            Log::debug('本日のリクエスト制限内です。処理を実行します。');
            Log::debug('    ');

         
            Log::debug('前回の制限から15分経過しています。処理を実行します');
            Log::debug('    ');
                    
            // APIのエンドポイントを叩きフォローする
            $result = $connection->post('friendships/destroy', [
                    'user_id' => $unfollow_target_id,
                    ]);
                   
            // Errorハンドリング
            // 通信成功時の処理
            if ($connection->getLastHttpCode() == 200) {
                     
                    // followsテーブルから削除
                $this->deleteFollowTable(Auth::user()->id, $unfollow_target_id);
            
                return response()->json(['success' => 'フォロー解除しました。'], 200);
            } else {
                // 通信失敗時の処理
                return response()->json(['error' => '問題が発生しました。時間を置いてから再度実行して下さい'], 500);
            }
        } else {
            Auth::user()->unfollow_limit_time = Carbon::now(); // 現在の時間を格納
            Auth::user()->unfollow_limit_count = $unfollow_limit_count;
            Auth::user()->update();
            Log::debug('本日のフォロー上限に到達しました。'. $unfollow_limit_count. '/20');
            Log::debug('    ');
            return response()->json(['error' => '本日のリクエスト上限に到達しました。'], 403);
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

    // Twitterアカウント未登録ユーザーを登録画面へ遷移させる（一度ログアウトさせる）
    public function registerRedirect()
    {
        // 認証済みユーザーを取得
        $user = Auth::user();
        // ログアウト
        Auth::logout();
        // セッションを削除
        session()->invalidate();
        // csrfトークンを再生成
        session()->regenerateToken();
        // Twitter認証画面へ遷移させる
        return redirect()->to('/register/twitter');
    }
}
