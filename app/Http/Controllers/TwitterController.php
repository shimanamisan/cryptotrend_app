<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TwitterController extends Controller
{
    public function getTweet()
    {
        //ツイートを5件取得
        $result = \Twitter::get('statuses/home_timeline', ["count" => 5]);

        dd($result);
    }
}
