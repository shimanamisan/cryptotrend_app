<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TwitterController extends Controller
{
    public function getTweet()
    {
        $query = 'ビットコイン';

        // 仮想通貨に関するツイートを検索
        $result = \Twitter::get('search/tweets', ['q' => $query, 'count' => 10])
            ->statuses;

        return view('twitter', ['result' => $result]);
    }
}
