<?php

namespace App\Http\Controllers;

use App\User; // ★追記
use App\SystemManager; // ★追記
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
    // 1日のフォロー上限の400を超えないように制御する（400に到達しないように、手前の値を設定）
    const DAY_FOLLOW_LIMIT = 395;
    // 1日にアプリ全体としてのフォロー制限を超えないように制御する
    const SYSTEM_FOLLOW_LIMIT = 990;
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
        /**
         * システムとしての1日のフォロー上限回数を超えた時の処理を追記する
         */
        
        // インスタンスを生成
        $connection = $this->twitterAuth();
        // フォローするユーザーのIDを格納
        $follow_target_id = $request->id;
        // 1日のフォロー上限を超えないように、現在のフォローした数を取得
        $follow_limit_count = Auth::user()->follow_limit_count;
        // 前回の15分毎のフォロー上限から15分後の値を格納（DBから引っ張ってきた値は数値ではなく数字なので変換する）
        $release_limit_time = (int)session()->get('follow_limit_time');
        // 現在の時間を格納
        $now_time = time();
        Log::debug('現在の時刻です：'.$now_time . '  制限の時間です：'.$release_limit_time);

        // 現在の時刻が15分毎14フォローの制限から、15分経過していたら処理を実行
        if($release_limit_time < $now_time){
        Log::debug('前回の制限から15分経過しています。処理を実行します');

            // ユーザー当たりの本日のリクエスト上限の範囲内か判定、超えていたらフォローの処理を行わずメッセージを返す
            if($follow_limit_count < self::DAY_FOLLOW_LIMIT){
                
                    // APIのエンドポイントを叩きフォローする
                    // $result = $connection->post('friendships/create', [
                    // 'user_id' => $follow_target_id,
                    // ]);
                    
                    // Errorハンドリング
                    // 通信成功時の処理
                    if ($connection->getLastHttpCode() == 200) {

                        // 既にフォローしているユーザーだった時の処理
                        if ($result->following) {
                            Log::debug('既にフォローしています；'. print_r($result, true));
                            // APIへのリクエストは通っているので、リミット数をカウントする。
                            ++$follow_limit_count;
                            Auth::user()->follow_limit_time = time(); // 現在の時間を格納
                            Auth::user()->follow_limit_count = $follow_limit_count;
                            Auth::user()->update();
                            return response()->json(['forbidden' => '既にフォローしているユーザーです'], 403);
                        }
                        // APIへのリクエスト後、リミット数をカウント
                        ++$follow_limit_count;
                        Auth::user()->follow_limit_time = time(); // 現在の時間を格納
                        Auth::user()->follow_limit_count = $follow_limit_count;
                        Auth::user()->update();
            
                        return response()->json(['success' => 'フォローしました！'], 200);
                    } else {
                        // 通信失敗時の処理
                        return response()->json(['error' => '時間を置いてから再度実行して下さい'], 500);
                    }
    
            }else{
                Log::debug('本日のフォロー上限に到達しました。'. $follow_limit_count. '/395');
                return response()->json(['error' => '本日のリクエスト上限に到達しました。'], 403);
            }

        }else{
            Log::debug('API制限中です。');
            return response()->json(['error' => 'API制限中です。しばらくお待ち下さい。'], 403);
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
        /**
         * 後にシステム全体での上限、990フォローを超えていたら実施しない処理を追記する
         */
        $SystemManager = SystemManager::where('id', 1)->first();

        $one_day_system_counter_limit = $SystemManager->one_day_system_counter;

        if($one_day_system_counter_limit < self::SYSTEM_FOLLOW_LIMIT){

            Log::debug('1日のアプリ全体としてのフォロー上限を以内なのでフォローを継続します '. $one_day_system_counter_limit.'/990回');
            Log::debug('    ');

            // DBからautofollowカラムが1のユーザーを取得
            $auto_follow_run_user_list = User::where('autofollow_status', self::AUTO_FOLLOW_STATUS_RUN)->get();
            Log::debug('=== 自動フォローステータスがONのユーザーを取得しています:handlメソッド ===');
            Log::debug(count($auto_follow_run_user_list). ' 人のユーザーが自動フォローをONにしています。');
    
            if($auto_follow_run_user_list->isEmpty()){
                Log::debug('自動フォローのユーザーが存在していないため処理を終了します');
                Log::debug('    ');
                exit();
            }
            foreach($auto_follow_run_user_list as $auto_follow_run_user_item) {
                $user = $auto_follow_run_user_item; // 各ユーザーのUserモデル
                $twitter_id = $auto_follow_run_user_item->my_twitter_id;
                $twitter_user_token = $auto_follow_run_user_item->twitter_token;
                $twitter_user_token_secret = $auto_follow_run_user_item->twitter_token_secret;
                $follow_limit_count = $auto_follow_run_user_item->follow_limit_count;
                
                if($follow_limit_count < self::DAY_FOLLOW_LIMIT){
                    Log::debug($user->name. ' さんはまだ1日の上限ではないので処理を継続します '.$follow_limit_count.'/395');
                    // 1ユーザー当たり1日の上限、395フォロー以下だったら自動フォローを実行する
                    // ステータスがONのユーザーの数だけ下記のオートフォローが実行される
                    $this->autoFollow($user, $twitter_id, $twitter_user_token, $twitter_user_token_secret, $follow_limit_count, $one_day_system_counter_limit, $SystemManager);
                    
                }else{
                    Log::debug($user->name.' さんは1日の上限を超えていたので処理を終了します '.$follow_limit_count.'/395');
                    Log::debug('    ');
                    continue;
                }
            }

        }else{
            Log::debug('1日のアプリ全体としてのフォロー上限を上回っているので処理を停止します '. $one_day_system_counter_limit.'/990回');
            Log::debug('    ');
            exit();
        }
        Log::debug('処理を終了します');
    }

    // 自動フォロー機能
    public function autoFollow($user, $twitter_id, $twitter_user_token, $twitter_user_token_secret, $follow_limit_count, $one_day_system_counter_limit, $SystemManager)
    {   
        // 1回の処理で15フォローを超えないようにする
        $system_follow_counter_quarter_minutes = 0;

        // インスタンスを生成
        $connect = $this->twitterOAuth($twitter_user_token, $twitter_user_token_secret);

        // DBに登録されているユーザを取得
        $twitterUserList = $this->getTwitterUser();

        // フォローしているユーザーを取得
        $follow_target = $this->fetchFollowTarget($twitter_id, $twitterUserList, $connect);
        // フォローしているユーザーのIDとDBに登録されているIDの差分を取得する。一致していないもの（フォローしていないユーザー）を取得する
        // 第一引数が比較元の配列、第二引数に比較する配列を指定する
        // 比較元の配列にしか無い値を取得する（第二引数の配列の値と一致したものは除外される）
        $follow_target_list = array_diff($follow_target, $twitterUserList);

        // dd($follow_target_list);
        // 全てフォローしてリストが空だったら処理を実施
        foreach($follow_target_list as $follow_target_id){

            // 
            if($follow_limit_count < self::DAY_FOLLOW_LIMIT && $one_day_system_counter_limit < self::SYSTEM_FOLLOW_LIMIT){
                Log::debug('1日のユーザーのフォロー制限回数です '. $follow_limit_count.'/395');
                Log::debug('1日のアプリ全体としてのフォロー制限回数です '. $one_day_system_counter_limit.'/990回');
                Log::debug('    ');
                /**
                 * アプリ全体として990フォローを超えないように自動フォロー中もカウントする必要がある
                 */
                ++$one_day_system_counter_limit;
                $SystemManager->one_day_system_counter = $one_day_system_counter_limit;
                $SystemManager->update();

                // 1日にフォローできる数をカウントしていく（usersテーブルより取得）
                ++$follow_limit_count;
                $user->follow_limit_count = $follow_limit_count;
                $user->update();

                // 15分毎に15フォローを超えないようにするカウンター
                ++$system_follow_counter_quarter_minutes;

                if($system_follow_counter_quarter_minutes < 15 ){
                    Log::debug('まだ '. $system_follow_counter_quarter_minutes . ' 回目のループなのでフォローを継続します');
                    
                    // $result = $connect->post('friendships/create', [
                    //     'user_id' => $follow_target_id
                    // ]);
                }else{
                    Log::debug($system_follow_counter_quarter_minutes . ' 回目、フォローの上限を超えました。処理を停止します。');
                    
                    $system_follow_counter_quarter_minutes = 0;
                    Log::debug('単位ユーザ当たりのカウンターをリセットします。$system_follow_counter_quarter_minutes：'. $system_follow_counter_quarter_minutes);
                 
                    Log::debug('次のユーザーへの処理へ以降、若しくは処理を停止します');
                    Log::debug('    ');
                    break;
                }
                Log::debug('連続でフォローしすぎてアカウント凍結されないように3秒間隔をあける');
                Log::debug('    ');
                sleep(3);

            }elseif($follow_limit_count == self::DAY_FOLLOW_LIMIT){
                Log::debug($user->name.' さんは1日のユーザーのフォロー制限回数を超えました '. $follow_limit_count.'/395');
                Log::debug('    ');
                $release_limit_time = time() + self::QUARTER_MINUTES;
                Log::debug('単位ユーザー当たりのAPI制限解除時間をDBへ保存します UNIX_TIME_STAMP: '.$release_limit_time);
                Log::debug('    ');
                $user->follow_limit_time = $release_limit_time;
                $user->update();
                break;
            }else{
                Log::debug('1日のアプリ全体としてのフォロー回数です '. $one_day_system_counter_limit.'/990');
                Log::debug('    ');
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
