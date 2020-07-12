<?php

namespace App\Http\Controllers;

use App\User; // ★追記
use App\SystemManager; // ★追記
use App\TwitterUser; // ★追記
use Illuminate\Http\Request;
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
    const AUTO_FOLLOW_LIMIT = 15;
    // 15分を秒に変換、個別でフォローする際の上限に使用する
    const QUARTER_MINUTES = 15 * 60;

    // フォローボタンからユーザーをフォローする
    public function follow(Request $request)
    {
               /**
         * システムとしての1日のフォロー上限回数を超えた時の処理を追記する
         */
        
        // インスタンスを生成
        $connection = $this->singleFollowAuth();

        // フォローするユーザーのIDを格納
        $follow_target_id = $request->id;


        // 1日のフォロー上限を超えないように、現在のフォローした数を取得
        $follow_limit_count = Auth::user()->follow_limit_count;

        // 前回の15分毎のフォロー上限から15分後の値を格納（DBから引っ張ってきた値は数値ではなく数字なので変換する）
        $release_limit_time = (int)session()->get('follow_limit_time');
        // 現在の時間を格納
        $now_time = time();
        Log::debug('現在の時刻です：'.$now_time . '  制限の時間です：'.$release_limit_time);
        Log::debug('    ');

        // ユーザー当たりの本日のリクエスト上限の範囲内か判定、超えていたらフォローの処理を行わずメッセージを返す
        if($follow_limit_count < self::DAY_FOLLOW_LIMIT){
            Log::debug('本日のリクエスト制限内です。処理を実行します。');
            Log::debug('    ');

            // 現在の時刻が15分毎14フォローの制限から、15分経過していたら処理を実行
            if($release_limit_time < $now_time){
                Log::debug('前回の制限から15分経過しています。処理を実行します');
                Log::debug('    ');
                    
                    // APIのエンドポイントを叩きフォローする
                    $result = $connection->post('friendships/create', [
                    'user_id' => $follow_target_id,
                    ]);
           
                    // dd($result);
                   
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

                    } else if($connection->getLastHttpCode() == 403){
                        \Log::debug('ステータスコード403の処理です');
                        // フォロー済みのユーザーだった場合は403のステータスコードを返す
                        return response()->json(['forbidden' => '既にフォローしているユーザーです'], 403);
                    }else{
                          // 通信失敗時の処理
                        return response()->json(['error' => '時間を置いてから再度実行して下さい'], 500);
                    }
            }else{

                Log::debug('API制限中です。');
                Log::debug('    ');
                return response()->json(['error' => 'API制限中です。しばらくお待ち下さい。'], 403);
            }

        }else{

            Log::debug('本日のフォロー上限に到達しました。'. $follow_limit_count. '/395');
            return response()->json(['error' => '本日のリクエスト上限に到達しました。'], 403);
            Log::debug('    ');
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

        //  \Log::debug($api_key);
        //  \Log::debug($api_key_secret);
        //  \Log::debug($access_token);
        //  \Log::debug($access_token_secret);
 
         $OAuth = new TwitterOAuth($api_key, $api_key_secret, $access_token, $access_token_secret);
 
         return $OAuth;
     }

 
}
