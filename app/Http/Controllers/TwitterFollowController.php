<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TwitterFollowController extends Controller
{
  public function follow($id)
  {
    // リクエストを送るURL
    $request_url = 'https://api.twitter.com/1.1/friendships/create.json';
    // パラメータ
    $param = [
      'user_id' => $id,
    ];
    $result = \Twitter::post();
  }
}
