<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TwitterFollowController extends Controller
{
  public function follow(Request $request)
  {
    $id = $request->id;
    // // リクエストを送るURL
    // $request_url = 'https://api.twitter.com/1.1/friendships/create.json';
    // パラメータ
    return response()->json(['success'], 200);
    // $param = [
    //   'user_id' => $id,
    // ];
    $result = \Twitter::post('friendships/create', [
      'user_id' => $id,
    ]);
  }
}
