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

    // 各ユーザーのtokenを元に、API接続前の認証処理を行うメソッド
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
        $follow_count = Auth::user()->follow_count;

        $result = $connection->post('friendships/create', [
      'user_id' => $follow_target_id,
    ]);

        if ($result->following) {
            Log::debug('既にフォローしています；'. print_r($result, true));
            return response()->json(['forbidden' => '既にフォローしているユーザーです'], 403);
        }

        return response()->json(['success' => 'フォローしました！'], 200);
    }

    // 自動フォローのON/OFFを切り替える
    public function autoFollowFlg(Request $request)
    {
        $user = Auth::user();
        // 自動フォローがONになっているか確認
        $autoFollow_flg = $request->status;
        // dd(gettype($autoFollow_flg));

        // ログイン中のユーザーでusersテーブルのautofollowが1だったら自動フォローする
        // そうでなければ処理を行わない
        if ($autoFollow_flg === 0) {
            $user->autofollow = ++$autoFollow_flg;
            $user->update();
            Log::debug('自動フォローをONにしました：' . $autoFollow_flg);
            return;
        } else {
            $user->autofollow = --$autoFollow_flg;
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
        // DBからautofollowカラムが1のユーザーを取得
        $auto_follow_run_user_list = User::where('autofollow', self::AUTO_FOLLOW_STATUS_RUN)->get();

        if($auto_follow_run_user_list->isEmpty()){
            Log::debug('自動フォローのユーザーが存在していないため処理を終了します');
            exit;
        }
        foreach ($auto_follow_run_user_list as $auto_follow_run_user_item) {
            $twitter_id = $auto_follow_run_user_item->my_twitter_id;
            $twitter_user_token = $auto_follow_run_user_item->twitter_token;
            $twitter_user_token_secret = $auto_follow_run_user_item->twitter_token_secret;
            Log::debug('=== 自動フォローステータスがONのユーザーを取得しています:handlメソッド ===');

            // ステータスがONのユーザーの数だけ下記のオートフォローが実行される
            
            $this->autoFollow($twitter_id, $twitter_user_token, $twitter_user_token_secret);
        }
    }

    // 自動フォロー機能
    public function autoFollow($twitter_id, $twitter_user_token, $twitter_user_token_secret)
    {   
        // 1回の処理で15フォローを超えないようにする
        $system_follow_counter = 0;

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

    

    }
    
    // 自分のフォローしているユーザーを取得する
    public function fetchFollowTarget($twitter_id, $twitterUserList, $connect)
    {       
        // 15分毎15リクエストが上限です
        $result = $connect->get('friends/ids', [
            'user_id' => $twitter_id
            ])->ids;

            Log::debug('取得結果 : ' .print_r($result, true));
        return $result;
    }

    // DBからフォローするためのユーザー情報を取得する
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
