<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite; // 追加

class CoinsController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function getCondInfo()
    {

    }

    // Twitter上で仮想通貨関連のツイートをしているユーザーを取得する処理
    // バッチ処理で定期的に実行
    public function searchCoins()
    {
        // 検索ワード
        $search_key = '"ビットコイン" OR "BTC"';

        // 仮想通貨銘柄に関するツイートを検索
        // consig/app.phpでエイリアスを設定しているので、useしなくてもバックスラッシュで読み込める 'Twitter' => App\Facades\Twitter::class,
        $result = \Twitter::get('search/tweets', [
            'q' => $search_key,
            'count' => 10,
            ])->statuses;

        dd($result);
    }
}
