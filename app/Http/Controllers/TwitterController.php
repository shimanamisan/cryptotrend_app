<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        \Log::debug('取得したツイートです：' . print_r($result, true));

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
        // 取得した情報を表示
        foreach ($result as $showData) {
            // \Log::debug('取得したデータ一覧です：' . print_r($showData, true));
        }

        foreach ($result as $tweet_all) {
            $twitter_user[] = [
                'id' => $tweet_all->user->id,
                'user_name' => $tweet_all->user->name,
                'account_name' => $tweet_all->user->screen_name,
                'new_tweet' => $tweet_all->text,
                'profile_message' => $tweet_all->user->description,
                'follow' => $tweet_all->user->friends_count,
                'follower' => $tweet_all->user->followers_count,
            ];
        }

        \Log::debug('取得したデータ一覧です：' . print_r($twitter_user, true));

        return view('userList', ['result' => $result]);
    }
}
