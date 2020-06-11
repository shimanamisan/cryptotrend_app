<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // ★追加

class TwitterController extends Controller
{
    public function searchTweet()
    {
        // 検索ワード
        $search_key = '"ビットコイン" OR "BTC"';

        // 仮想通貨銘柄に関するツイートを検索
        $result = \Twitter::get('search/tweets', [
            'q' => $search_key,
            'count' => 10,
        ])->statuses;

        Log::debug('取得したツイートです：' . print_r($result, true));

        return view('twitter', ['result' => $result]);
    }

    // 関連キーワードをつぶやいているユーザーを取得
    public function userList()
    {
        // 検索ワード
        $search_key = '仮想通貨';
        $search_limit_count = 100;

        $options = [
            'q' => $search_key,
            'count' => $search_limit_count,
        ];

        // 仮想通貨に関するツイートを検索
        $result = \Twitter::get('search/tweets', $options)->statuses;

        Log::debug('取得したツイートです：' . print_r($result, true));

        return view('userlist');
    }
}
