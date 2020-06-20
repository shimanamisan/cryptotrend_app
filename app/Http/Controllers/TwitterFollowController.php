<?php

namespace App\Http\Controllers;

use App\TwitterUser; // ★追記
use App\User; // ★追記
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ★追記
use Abraham\TwitterOAuth\TwitterOAuth; // ★追記

class TwitterFollowController extends Controller
{
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

    $result = $connection->post('friendships/create', [
      'user_id' => $follow_target_id,
    ]);

    if ($result->following) {
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
      \Log::debug('自動フォローをONにしました：' . $autoFollow_flg);
      return;
    } else {
      $user->autofollow = --$autoFollow_flg;
      $user->update();
      \Log::debug('自動フォローをOFFにしました：' . $autoFollow_flg);
      return;
    }
  }

  // 自動フォロー機能
  public function autoFollow()
  {
    // インスタンスを生成
    $connection = $this->twitterAuth();

    \Log::debug('オートフォローを開始します。');
    // DBからautofollowカラムが1のユーザーを取得
    $auto_follow_user = User::where('autofollow', 1)->get();

    // 既にフォロー済みのアカウントを配列に格納
    $friend_data = $connection->get('friends/ids');

    // DBに登録されているユーザを取得
    $TwitterUser = new TwitterUser();
    $dbresult = $TwitterUser->all();

    foreach ($dbresult as $search_result_item) {
      $twitter_user[] = $search_result_item->twitter_id;
    }

    // $diff = array_diff($friend_data->ids, $dbresult);

    // dd($diff);
    // dd($twitter_user);
    // dd($dbresult);
    dd($friend_data);
    // DBからTwitterの情報を取得してきて自分のフォロワーと見比べてフォローしていなかったら新規でフォローする
    // リクエスト/24時間、ユーザーあたり400：アプリごとに1000となっている
    // フォローの上限数は15分毎日14とする
    // 1日に1000以上フォローしなようにする
  }
}
