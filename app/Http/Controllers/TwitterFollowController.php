<?php

namespace App\Http\Controllers;

use App\User; // ★追記
use App\TwitterUser; // ★追記
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // ★追記
use Abraham\TwitterOAuth\TwitterOAuth; // ★追記

class TwitterFollowController extends Controller
{
    // 自動フォローのステータス
    const AUTO_FOLLOW_STATUS_RUN = 1;
    const AUTO_FOLLOW_STATUS_STOP = 0;
    // 1日のフォロー上限の400を超えない異様にする
    const DAY_FOLLOW_LIMIT = 400;
    // 15分を秒に変換、個別でフォローする際の上限に使用する
    const QUARTER_MINUTES = 15 * 60;
        


    // 認証済みのユーザーのtokenを元に、API接続前の認証処理を行うメソッド
    public function twitterAuth()
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

    // ユーザーをフォローするメソッド
    public function follow(Request $request)
    {
        // インスタンスを生成
        $connection = $this->twitterAuth();
        // フォローするユーザーのIDを格納
        $follow_target_id = $request->id;
        // 1日のフォロー上限を超えないように、現在のフォローした数を取得
        $follow_limit_count = Auth::user()->follow_limit_count;

        $result = $connection->post('friendships/create', [
          'user_id' => $follow_target_id,
         ]);
        if($connection->getLastHttpCode() === 200){
            // 通信成功時の処理
            if ($result->following) {
                Log::debug('既にフォローしています；'. print_r($result, true));
                return response()->json(['forbidden' => '既にフォローしているユーザーです'], 403);
            }
            
            ++$follow_limit_count;
            Auth::user()->follow_limit_count = $follow_limit_count;
            Auth::user()->update();

            return response()->json(['success' => 'フォローしました！'], 200);            
        }else{
            // 通信失敗時の処理
            return response()->json(['error' => 'Errorが発生しています。']);

        }
    }

    // 自動フォローのON/OFFを切り替える
    public function autoFollowFlg(Request $request)
    {
        $user = Auth::user();
        // 自動フォローがONになっているか確認
        $autoFollow_flg = $request->status;

        // ログイン中のユーザーでusersテーブルのautofollowが1だったら自動フォローする
        // そうでなければ処理を行わない
        if ($autoFollow_flg === 0) {
            $user->autofollow_status = ++$autoFollow_flg;
            $user->update();
            Log::debug('自動フォローをONにしました：' . $autoFollow_flg);
            return;
        } else {
            $user->autofollow_status = --$autoFollow_flg;
            $user->update();
            Log::debug('自動フォローをOFFにしました：' . $autoFollow_flg);
            return;
        }
    }

    // DBからTwitterの情報を取得してきて自分のフォロワーと見比べてフォローしていなかったら新規でフォローする
    // リクエスト/24時間、ユーザーあたり400：アプリごとに1000となっている
    // フォローの上限数は15分毎日14とする
    // 1日に1000以上フォローしなようにする

    // 自動フォロー機能呼び出し
    public function handl()
    {
        $now_time = time(); // 現在の時刻

        /**
         * 後にシステム全体での上限、1000フォローを超えていたら実施しない処理を追記する
         */

        // DBからautofollowカラムが1のユーザーを取得
        $auto_follow_run_user_list = User::where('autofollow_status', self::AUTO_FOLLOW_STATUS_RUN)->get();
        Log::debug('=== 自動フォローステータスがONのユーザーを取得しています:handlメソッド ===');
        Log::debug(count($auto_follow_run_user_list). ' 人のユーザーが自動フォローをONにしています。');

        if($auto_follow_run_user_list->isEmpty()){
            Log::debug('自動フォローのユーザーが存在していないため処理を終了します');
            exit;
        }
        foreach ($auto_follow_run_user_list as $auto_follow_run_user_item) {
            $user = $auto_follow_run_user_item; // 各ユーザーのUserモデル
            $twitter_id = $auto_follow_run_user_item->my_twitter_id;
            $twitter_user_token = $auto_follow_run_user_item->twitter_token;
            $twitter_user_token_secret = $auto_follow_run_user_item->twitter_token_secret;
            $follow_limit_count = $auto_follow_run_user_item->follow_limit_count;
            
            if($follow_limit_count < self::DAY_FOLLOW_LIMIT ){
                Log::debug($user->name. ' さんはまだ1日の上限ではないので処理を継続します '.$follow_limit_count.'/400');
                // 1ユーザー当たり1日の上限、400フォロー以下だったら自動フォローを実行する
                // ステータスがONのユーザーの数だけ下記のオートフォローが実行される
                $this->autoFollow($user, $twitter_id, $twitter_user_token, $twitter_user_token_secret, $follow_limit_count);
                
            }else{
                Log::debug($user->name.' さんは1日の上限を超えていたので処理を終了します '.$follow_limit_count.'/400');
                continue;
            }
         
            

        }
    }

    // 自動フォロー機能
    public function autoFollow($user, $twitter_id, $twitter_user_token, $twitter_user_token_secret, $follow_limit_count)
    {   
        // 1回の処理で15フォローを超えないようにする
        $system_follow_counter_quarter_minutes = 0;

        // インスタンスを生成
        $connect = $this->twitterOAuth($twitter_user_token, $twitter_user_token_secret);
        Log::debug('=== 自動フォローの処理を開始します:autoFollowメソッド ===');

        // DBに登録されているユーザを取得
        $twitterUserList = $this->getTwitterUser();
        Log::debug('=== フォローターゲットが格納されています:autoFollowメソッド ===');

        // フォローしているユーザーを取得
        $follow_target = $this->fetchFollowTarget($twitter_id, $twitterUserList, $connect);
        // フォローしていないユーザーを抽出する
        $follow_target_list = array_diff($twitterUserList, $follow_target);

        // dd($follow_target_list);
       
        foreach($follow_target_list as $follow_target_id){
            // 15分毎に15フォローを超えないようにするカウンター
            ++$system_follow_counter_quarter_minutes;

            if($system_follow_counter_quarter_minutes < 15){
                Log::debug('まだ '. $system_follow_counter_quarter_minutes . ' 回目のループなのでフォローを継続します');
                // $result = $connect->post('friendships/create', [
                //     'user_id' => $follow_target_id
                // ]);

                // 1日にフォローできる数をカウントしていく（usersテーブルより取得）
                ++$follow_limit_count;
                $user->follow_limit_count = $follow_limit_count;
                $user->update();
                /**
                 * アプリ全体として1000フォローを超えないように自動フォロー中もカウントする必要がある
                 */
            }else{
                Log::debug($system_follow_counter_quarter_minutes . ' 回目、フォローの上限を超えました。処理を停止します。');
                $system_follow_counter_quarter_minutes = 0;
                Log::debug('カウンターをリセットして、単位ユーザ当たりのカウンターをリセットします。$system_follow_counter_quarter_minutes：'. $system_follow_counter_quarter_minutes);
                break;
            }

        }

    }
    
    // 自分のフォローしているユーザーを取得する
    public function fetchFollowTarget($twitter_id, $twitterUserList, $connect)
    {   
        // 15分毎15リクエストが上限です
        $result = $connect->get('friends/ids', [
            'user_id' => $twitter_id
            ])->ids;
            // Log::debug('取得結果 : ' .print_r($result, true));
        return $result;
    }

    // DBからTwitterユーザー情報を取得する
    public function getTwitterUser()
    {
        // DBより仮想通貨関連のアカウントを取得
        $dbresult = TwitterUser::all();

        foreach ($dbresult as $item) {
            $twitterUserList[] = $item->twitter_id;
        }
        
        return $twitterUserList;
    }

    // インスタンスを生成
    public function twitterOAuth($twitter_user_token, $twitter_user_token_secret)
    {
        Log::debug('=== インスタンスを生成します === ');

            // ヘルパー関数のconfigメソッドを通じて、config/services.phpのtwitterの登録した中身を参照
            $config = config('services.twitter');
            // APIキーを格納
            $api_key = $config['client_id'];
            $api_key_secret = $config['client_secret'];
            // アクセストークンを格納
            $access_token = $twitter_user_token;
            $access_token_secret = $twitter_user_token_secret;
    
            $OAuth = new TwitterOAuth($api_key, $api_key_secret, $access_token, $access_token_secret);
    
            return $OAuth;
    }
}
