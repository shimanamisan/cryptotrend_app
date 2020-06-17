<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TwitterFollowController extends Controller
{
  // 各ユーザーのtokenを元に、API接続前の認証処理を行うメソッド
  public function twitterAuth()
  {
    //
  }

  // ユーザーをフォローするメソッド
  public function follow(Request $request)
  {
    $id = $request->id;

    $result = \Twitter::post('friendships/create', [
      'user_id' => $id,
    ]);

    // $test = \Twitter::get('followers/list', ['count' => 200]);

    // foreach ($test as $i) {
    //   \Log::debug('自分のフォロワーを取得：' . print_r($i, true));
    // }
  }

  // 自動フォローするメソッド
  public function autoFollow()
  {
    //
  }
}
