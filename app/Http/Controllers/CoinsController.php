<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite; // 追加

class CoinsController extends Controller
{
    public function index()
    {
        return view('coins');
    }

    public function getCondInfo()
    {

    }

    // Twitter上で仮想通貨関連のツイートをしているユーザーを取得する処理
    // バッチ処理で定期的に実行
    public function searchCoins()
    {
        // 検索ワード
        $search_key = [
            0 => '"ビットコイン" OR "Btc"',
            1 => '"イーサリアム" OR "Eth"',
            2 => '"イーサリアムクラシック" OR "Etc"',
            3 => '"仮想通貨リスク" OR "Lisk',
        ];
        // $search_key = '"仮想通貨" OR "ビットコイン" OR "Btc" OR 
        // "イーサリアム" OR "Eth" OR "イーサリアムクラシック" OR "Etc" OR "仮想通貨リスク" OR "Lisk" OR
        // "ファクトム" OR "Fct" OR "リップル" OR "Xrp" OR "ネム" OR "Nem " OR "ライトコイン" OR "Ltc" 
        // OR "ビットコインキャッシュ" OR "Bch" OR "モナコイン" OR "Mona" OR "仮想通貨ダッシュ” OR "Dash" 
        // OR "ジーキャッシュ" OR "Zec" OR "モネロ" OR "Xmr" OR "オーガー" OR "Rep"';
 
        for($i=0; $i < count($search_key); $i++){
            
            $result[] = \Twitter::get('search/tweets', [
                'q' => $search_key,
                'count' => 100,
                ])->statuses;
        }
        // 仮想通貨銘柄に関するツイートを検索
        // consig/app.phpでエイリアスを設定しているので、useしなくてもバックスラッシュで読み込める 'Twitter' => App\Facades\Twitter::class,

        dd($result);
    }
}
