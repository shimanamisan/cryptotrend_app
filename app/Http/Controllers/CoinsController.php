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
            0 => '"ビットコイン" OR "$BTC" OR "#BTC"',
            1 => '"イーサリアム" OR "$ETH" OR "#ETH"',
            2 => '"イーサリアムクラシック" OR "$ETC" OR "#ETC"',
            3 => '"リップル" OR "$XRP" OR "#XRP"',
            4 => '"ネム" OR "$NEM" OR "#NEM"',
            5 => '"ライトコイン" OR "$LTC" OR "#LTC"',
            6 => '"ビットコインキャッシュ" OR "$BCH" OR "#BCH"',
            7 => '"仮想通貨ダッシュ”" OR "$MONA" OR "#MONA"',
            8 => '"ジーキャッシュ" OR "$DASH" OR "#DASH"',
            9 => '"モネロ" OR "$XRM" OR "#XRM"',
        ];
        // $search_key = '"仮想通貨" OR "ビットコイン" OR "Btc" OR 
        // "イーサリアム" OR "Eth" OR "イーサリアムクラシック" OR "Etc" OR "仮想通貨リスク" OR "Lisk" OR
        // "ファクトム" OR "Fct" OR "リップル" OR "Xrp" OR "ネム" OR "Nem " OR "ライトコイン" OR "Ltc" 
        // OR "ビットコインキャッシュ" OR "Bch" OR "モナコイン" OR "Mona" OR "仮想通貨ダッシュ” OR "Dash" 
        // OR "ジーキャッシュ" OR "Zec" OR "モネロ" OR "Xmr" OR "オーガー" OR "Rep"';
 
        // 銘柄ごとにツイートを取得する
        for($i=0; $i < count($search_key); $i++){
            
            // 仮想通貨銘柄に関するツイートを検索
            // consig/app.phpでエイリアスを設定しているので、useしなくてもバックスラッシュで読み込める
            // 'Twitter' => App\Facades\Twitter::class,
            $result[] = \Twitter::get('search/tweets', [
                'q' => $search_key,
                'count' => 100,
                'result_type' => 'recent' // 取得するツイートの種類（recent＝最新のツイート）
                ])->statuses;
        }

        dd($result);

        foreach($result as $tweet_item){

        }
        
    }
}
