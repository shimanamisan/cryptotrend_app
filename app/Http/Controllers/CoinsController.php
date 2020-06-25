<?php

namespace App\Http\Controllers;

use Carbon\Carbon; // ★追記
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite; // 追加

class CoinsController extends Controller
{
    // search/tweetsのリクエストの上限は、15分毎180回
    const REQUEST_LIMIT = 180;
    const REQUEST_LIMIT＿MINUTES = 15;

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
        $date = new Carbon('+15 minutes');
        // 検索ワード（ハッシュタグまで検索に含める）
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
      
        for($i = 0; $i<15; $i++){

            // 仮想通貨銘柄に関するツイートを検索
            // consig/app.phpでエイリアスを設定しているので、useしなくてもバックスラッシュで読み込める
            // 'Twitter' => App\Facades\Twitter::class,
            $params = [
                'q' => $search_key[$i],
                'count' => 100,
                'result_type' => 'recent' // 取得するツイートの種類（recent＝最新のツイート）
            ];

            \Log::debug('変換前のparamです。'. print_r($params, true));
            
            // オブジェクト形式で返ってくる
            $result_obj = \Twitter::get('search/tweets', $params);

            // オブジェクトを配列に変換
            $result_arr = json_decode(json_encode($result_obj), true);

                // ツイート本文を抽出
                for($h = 0; $h < count($result_arr['statuses']); $h++){
                    $tweet_text[] = $result_arr['statuses'][$h]['text'];

                }
                // next_resultsがなければ処理を終了
                if(empty($result_arr['search_metadata']['next_results'])){
                    break;
                }

                // パラメータの先頭の？を除去（次のページの）
                $next_results = preg_replace('/^\?/', '', $result_arr['search_metadata']['next_results']);
                
                // パラメータに変換
                parse_str($next_results, $params);

        }
            dd($tweet_text);
     
        
        
    }
}
