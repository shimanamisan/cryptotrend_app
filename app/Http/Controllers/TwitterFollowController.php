<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    $OAuth = $this->twitterAuth();

    // フォローするユーザーのIDを格納
    $follow_target_id = $request->id;

    $result = $OAuth->post('friendships/create', [
      'user_id' => $follow_target_id,
    ]);

    if ($result->following) {
      return response()->json(['forbidden' => '既にフォローしているユーザーです'], 403);
    }

    return response()->json(['success' => 'フォローしました！'], 200);
  }

  // 自動フォローするメソッド
  public function autoFollow()
  {
    //
  }
}
