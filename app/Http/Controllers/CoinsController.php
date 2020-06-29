<?php

namespace App\Http\Controllers;

use App\Coin; // ★追記
use App\Trend; // ★追記
use Carbon\Carbon; // ★追記
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite; // 追加

class CoinsController extends Controller
{
    // search/tweetsのリクエストの上限は、15分毎180回
    const REQUEST_LIMIT = 180;
    const REQUEST_LIMIT＿MINUTES = 15;

    public function index(Coin $coin)
    {
        $coins = DB::table('coins')
        ->join('trends', 'coins.id', '=', 'trends.coin_id')
        ->get();
        $coinj_json = json_encode($coins);

        return view('coins', ['coins' => $coinj_json]);
    }

    // Twitter上で仮想通貨関連のツイートをしているユーザーを取得する処理
    // 30分毎にバッチ処理で定期的に実行
    public function hour(Coin $coin)
    {   
   
        // 検索ワード（ハッシュタグまで検索に含める）
        $search_key = [
            0 => '"ビットコイン" OR "$BTC" OR "#BTC"',
            1 => '"イーサリアム" OR "$ETH" OR "#ETH"',
            2 => '"イーサリアムクラシック" OR "$ETC" OR "#ETC"',
            3 => '"リスク" OR "$LSK" OR "#LSK"',
            4 => '"ファクトム" OR "$FCT" OR "#FCT"',
            5 => '"リップル" OR "$XRP" OR "#XRP"',
            6 => '"ネム" OR "$XEM" OR "#XEM"',
            7 => '"ライトコイン" OR "$LTC" OR "#LTC"',
            8 => '"ビットコインキャッシュ" OR "$BCH" OR "#BCH"',
            9 => '"モナコイン" OR "$MONA" OR "#MONA"',
            10 => '"ステラルーメン" OR "$XLM" OR "#XLM"',
            11 => '"クアンタム" OR "$QTUM" OR "#QTUM"',
        ];

        // 15分毎のリクエストの回数をカウントしていく
        $request_limit_quarter_count = 0;
        // 15分毎180回制限のリクエストをカウントしていく
        $request_total_limit = 0;
       
        // ツイートを取得する期間を設定
        // このような形式にする：since:2018-12-31_23:59:59_JST until:2019-01-01_00:00:00_JST
        $now_time = date("Y-m-d_H:i:s")."_JST";//今の時間
        // dd($now_time);

        $before_hour = date('Y-m-d_H:i:s', strtotime('-1 hour', time()))."_JST";//カウント開始の時間
        // dd($before_hour);

    
        // search_keyに格納した銘柄ごとにツイートを取得する
        for($i = 0; $i < count($search_key); $i++){

            // 仮想通貨銘柄に関するツイートを検索
            // consig/app.phpでエイリアスを設定しているので、useしなくてもバックスラッシュで読み込める
            // 'Twitter' => App\Facades\Twitter::class,
            $params = [
                'q' => $search_key[$i],
                'count' => 100,
                'result_type' => 'recent', // 取得するツイートの種類（recent＝最新のツイート）
                'since' => $before_hour, // 指定日時以降のツイートを取得
                'until' => $now_time // 指定日時以前のツイートを取得
            ];

            \Log::debug($search_key[$i]. ' の検索が始まっています。');
            \Log::debug('  ');

            // ツイートを取得してく
            for($k = 0; $k < self::REQUEST_LIMIT＿MINUTES; $k++){

                // リクエストの上限値に来たら処理を停止
                if($request_limit_quarter_count == self::REQUEST_LIMIT＿MINUTES){
                    \Log::debug('15分毎15回のリクエスト上限に到達しました');
                    \Log::debug('  ');
                    // リクエストをリセット
                    $request_limit_quarter_count = 0;
                    break;
                }
                
                // オブジェクト形式で返ってくる
                $result_obj = \Twitter::get('search/tweets', $params);
                // dd($result_obj);

                // リクエストをカウント
                ++$request_limit_quarter_count;
                \Log::debug('リクエスト数をカウントしています：'. $request_limit_quarter_count .' 回');
    
                // オブジェクトを配列に変換
                $result_arr = json_decode(json_encode($result_obj), true);
                    // ツイート本文を抽出
                    for($h = 0; $h < count($result_arr['statuses']); $h++){
                        
                        $tweet_text[] = $result_arr['statuses'][$h]['text'];
                    }
                    
                    // next_resultsがなければ処理を終了
                    if(empty($result_arr['search_metadata']['next_results'])){
                        \Log::debug('検索結果が空になったので次の処理へ移ります');
                        \Log::debug('  ');
                        // リクエストをリセット
                        $request_limit_quarter_count = 0;
                        break;
                    }
    
                    // パラメータの先頭の？を除去（次のページの）
                    $next_results = preg_replace('/^\?/', '', $result_arr['search_metadata']['next_results']);
                    
                    // パラメータに変換
                    parse_str($next_results, $params);

            }

            \Log::debug($search_key[$i]. ' の結果をDBへ保存します これは添字です '. $i . ' DB登録に使用する際は+1して使用して下さい');

            // 各銘柄ごとにツイート数をカウントしてDBへ保存する
            // 銘柄の集計結果
            $trend_count = count($tweet_text);
            // 添字なのでDB保存用に+1しておく
            ++$i; 
            $coinObj = $coin->find($i);
            // updateOrCreateメソッド：第一引数に指定したカラムに値が存在していれば更新し、無ければ新規登録する
            $coinObj->trends()->updateOrCreate(
                ['coin_id' => $i],
                ['hour' => $trend_count]);

            // カウントしたままだと次の通貨を飛ばしてしまうのでデクリメントしておく
            --$i;
            \Log::debug('登録が完了しました。次の銘柄へ移ります');
            \Log::debug('  ');

            // カウントした値を初期化する
            $tweet_text = [];
            // リクエスト回数をリセット
            $request_limit_quarter_count = 0;
        }

        \Log::debug('ここの処理は最後かな？');
        \Log::debug('  ');

    }

    // 1日のツイート数を集計するメソッド
    public function day(Coin $coin)
    {   
   
        // 検索ワード（ハッシュタグまで検索に含める）
        $search_key = [
            0 => '"ビットコイン" OR "$BTC" OR "#BTC"',
            1 => '"イーサリアム" OR "$ETH" OR "#ETH"',
            2 => '"イーサリアムクラシック" OR "$ETC" OR "#ETC"',
            3 => '"リスク" OR "$LSK" OR "#LSK"',
            4 => '"ファクトム" OR "$FCT" OR "#FCT"',
            5 => '"リップル" OR "$XRP" OR "#XRP"',
            6 => '"ネム" OR "$XEM" OR "#XEM"',
            7 => '"ライトコイン" OR "$LTC" OR "#LTC"',
            8 => '"ビットコインキャッシュ" OR "$BCH" OR "#BCH"',
            9 => '"モナコイン" OR "$MONA" OR "#MONA"',
            10 => '"ステラルーメン" OR "$XLM" OR "#XLM"',
            11 => '"クアンタム" OR "$QTUM" OR "#QTUM"',
        ];

        // 15分毎のリクエストの回数をカウントしていく
        $request_limit_quarter_count = 0;
        // 15分毎180回制限のリクエストをカウントしていく
        $request_total_limit = 0;
       
        // ツイートを取得する期間を設定
        // このような形式にする：since:2018-12-31_23:59:59_JST until:2019-01-01_00:00:00_JST
        $now_time = date("Y-m-d_H:i:s")."_JST";//今の時間
        // dd($now_time);

        $before_hour = date('Y-m-d_H:i:s', strtotime('-1 day', time()))."_JST";//カウント開始の時間

    
        // search_keyに格納した銘柄ごとにツイートを取得する
        for($i = 0; $i < count($search_key); $i++){

            // 仮想通貨銘柄に関するツイートを検索
            // consig/app.phpでエイリアスを設定しているので、useしなくてもバックスラッシュで読み込める
            // 'Twitter' => App\Facades\Twitter::class,
            $params = [
                'q' => $search_key[$i],
                'count' => 100,
                'result_type' => 'recent', // 取得するツイートの種類（recent＝最新のツイート）
                'since' => $before_hour, // 指定日時以降のツイートを取得
                'until' => $now_time // 指定日時以前のツイートを取得
            ];

            \Log::debug($search_key[$i]. ' の検索が始まっています。');
            \Log::debug('  ');

            // ツイートを取得してく
            for($k = 0; $k < self::REQUEST_LIMIT＿MINUTES; $k++){

                // リクエストの上限値に来たら処理を停止
                if($request_limit_quarter_count == self::REQUEST_LIMIT＿MINUTES){
                    \Log::debug('15分毎15回のリクエスト上限に到達しました');
                    \Log::debug('  ');
                    // リクエストをリセット
                    $request_limit_quarter_count = 0;
                    break;
                }
                
                // オブジェクト形式で返ってくる
                $result_obj = \Twitter::get('search/tweets', $params);
                \Log::debug('データの型を取得しています '. gettype($result_obj));
                \Log::debug('  ');
                // dd($result_obj);

                // リクエストをカウント
                ++$request_limit_quarter_count;
                \Log::debug('リクエスト数をカウントしています：'. $request_limit_quarter_count .' 回');
    
                // オブジェクトを配列に変換
                $result_arr = json_decode(json_encode($result_obj), true);

                    // ツイート本文を抽出
                    for($h = 0; $h < count($result_arr['statuses']); $h++){
                        
                        $tweet_text[] = $result_arr['statuses'][$h]['text'];
                    }
                    
                    // next_resultsがなければ処理を終了
                    if(empty($result_arr['search_metadata']['next_results'])){
                        \Log::debug('検索結果が空になったので次の処理へ移ります');
                        \Log::debug('  ');
                        // リクエストをリセット
                        $request_limit_quarter_count = 0;
                        break;
                    }
    
                    // パラメータの先頭の？を除去（次のページの）
                    $next_results = preg_replace('/^\?/', '', $result_arr['search_metadata']['next_results']);
                    
                    // パラメータに変換
                    parse_str($next_results, $params);

            }

            \Log::debug($search_key[$i]. ' の結果をDBへ保存します これは添字です '. $i . ' DB登録に使用する際は+1して使用して下さい');

            // 各銘柄ごとにツイート数をカウントしてDBへ保存する
            // 銘柄の集計結果
            $trend_count = count($tweet_text);
            // 添字なのでDB保存用に+1しておく
            ++$i; 
            $coinObj = $coin->find($i);
            // updateOrCreateメソッド：第一引数に指定したカラムに値が存在していれば更新し、無ければ新規登録する
            $coinObj->trends()->updateOrCreate(
                ['coin_id' => $i],
                ['day' => $trend_count]);

            // カウントしたままだと次の通貨を飛ばしてしまうのでデクリメントしておく
            --$i;
            \Log::debug('登録が完了しました。次の銘柄へ移ります');
            \Log::debug('  ');

            // カウントした値を初期化する
            $tweet_text = [];
            // リクエスト回数をリセット
            $request_limit_quarter_count = 0;
        }

        \Log::debug('ここの処理は最後かな？');
        \Log::debug('  ');

    }


    public function week(Coin $coin)
    {   
   
        // 検索ワード（ハッシュタグまで検索に含める）
        $search_key = [
            0 => '"ビットコイン" OR "$BTC" OR "#BTC"',
            1 => '"イーサリアム" OR "$ETH" OR "#ETH"',
            2 => '"イーサリアムクラシック" OR "$ETC" OR "#ETC"',
            3 => '"リスク" OR "$LSK" OR "#LSK"',
            4 => '"ファクトム" OR "$FCT" OR "#FCT"',
            5 => '"リップル" OR "$XRP" OR "#XRP"',
            6 => '"ネム" OR "$XEM" OR "#XEM"',
            7 => '"ライトコイン" OR "$LTC" OR "#LTC"',
            8 => '"ビットコインキャッシュ" OR "$BCH" OR "#BCH"',
            9 => '"モナコイン" OR "$MONA" OR "#MONA"',
            10 => '"ステラルーメン" OR "$XLM" OR "#XLM"',
            11 => '"クアンタム" OR "$QTUM" OR "#QTUM"',

        ];

        // 15分毎のリクエストの回数をカウントしていく
        $request_limit_quarter_count = 0;
        // 15分毎180回制限のリクエストをカウントしていく
        $request_total_limit = 0;
       
        // ツイートを取得する期間を設定
        // このような形式にする：since:2018-12-31_23:59:59_JST until:2019-01-01_00:00:00_JST
        $now_time = date("Y-m-d_H:i:s")."_JST";//今の時間
        // dd($now_time);

        $before_hour = date('Y-m-d_H:i:s', strtotime('-1 week', time()))."_JST";//カウント開始の時間
        // dd($before_hour);

    
        // search_keyに格納した銘柄ごとにツイートを取得する
        for($i = 0; $i < count($search_key); $i++){

            // 仮想通貨銘柄に関するツイートを検索
            // consig/app.phpでエイリアスを設定しているので、useしなくてもバックスラッシュで読み込める
            // 'Twitter' => App\Facades\Twitter::class,
            $params = [
                'q' => $search_key[$i],
                'count' => 100,
                'result_type' => 'recent', // 取得するツイートの種類（recent＝最新のツイート）
                'since' => $before_hour, // 指定日時以降のツイートを取得
                'until' => $now_time // 指定日時以前のツイートを取得
            ];

            \Log::debug($search_key[$i]. ' の検索が始まっています。');
            \Log::debug('  ');

            // ツイートを取得してく
            for($k = 0; $k < self::REQUEST_LIMIT＿MINUTES; $k++){

                // リクエストの上限値に来たら処理を停止
                if($request_limit_quarter_count == self::REQUEST_LIMIT＿MINUTES){
                    \Log::debug('15分毎15回のリクエスト上限に到達しました');
                    \Log::debug('  ');
                    // リクエストをリセット
                    $request_limit_quarter_count = 0;
                    break;
                }
                
                // オブジェクト形式で返ってくる
                $result_obj = \Twitter::get('search/tweets', $params);
                // dd($result_obj);

                // リクエストをカウント
                ++$request_limit_quarter_count;
                \Log::debug('リクエスト数をカウントしています：'. $request_limit_quarter_count .' 回');
    
                // オブジェクトを配列に変換
                $result_arr = json_decode(json_encode($result_obj), true);
                    // ツイート本文を抽出
                    for($h = 0; $h < count($result_arr['statuses']); $h++){
                        
                        $tweet_text[] = $result_arr['statuses'][$h]['text'];
                    }
                    
                    // next_resultsがなければ処理を終了
                    if(empty($result_arr['search_metadata']['next_results'])){
                        \Log::debug('検索結果が空になったので次の処理へ移ります');
                        \Log::debug('  ');
                        // リクエストをリセット
                        $request_limit_quarter_count = 0;
                        break;
                    }
    
                    // パラメータの先頭の？を除去（次のページの）
                    $next_results = preg_replace('/^\?/', '', $result_arr['search_metadata']['next_results']);
                    
                    // パラメータに変換
                    parse_str($next_results, $params);

            }

            \Log::debug($search_key[$i]. ' の結果をDBへ保存します これは添字です '. $i . ' DB登録に使用する際は+1して使用して下さい');

            // 各銘柄ごとにツイート数をカウントしてDBへ保存する
            // 銘柄の集計結果
            $trend_count = count($tweet_text);
            // 添字なのでDB保存用に+1しておく
            ++$i; 
            $coinObj = $coin->find($i);
            // updateOrCreateメソッド：第一引数に指定したカラムに値が存在していれば更新し、無ければ新規登録する
            $coinObj->trends()->updateOrCreate(
                ['coin_id' => $i],
                ['week' => $trend_count]);

            // カウントしたままだと次の通貨を飛ばしてしまうのでデクリメントしておく
            --$i;
            \Log::debug('登録が完了しました。次の銘柄へ移ります');
            \Log::debug('  ');

            // カウントした値を初期化する
            $tweet_text = [];
            // リクエスト回数をリセット
            $request_limit_quarter_count = 0;
        }

        \Log::debug('ここの処理は最後かな？');
        \Log::debug('  ');

    }

    // 引数を与えることで、1時間・1日・1週間のデータを取得を分けるようにする
    public function getTrendTweet(Coin $coin, $period)
    {   
        
        // 検索ワード（ハッシュタグまで検索に含める）
        $search_key = [
            0 => '"ビットコイン" OR "$BTC" OR "#BTC"',
            1 => '"イーサリアム" OR "$ETH" OR "#ETH"',
            2 => '"イーサリアムクラシック" OR "$ETC" OR "#ETC"',
            3 => '"リスク" OR "$LSK" OR "#LSK"',
            4 => '"ファクトム" OR "$FCT" OR "#FCT"',
            5 => '"リップル" OR "$XRP" OR "#XRP"',
            6 => '"ネム" OR "$XEM" OR "#XEM"',
            7 => '"ライトコイン" OR "$LTC" OR "#LTC"',
            8 => '"ビットコインキャッシュ" OR "$BCH" OR "#BCH"',
            9 => '"モナコイン" OR "$MONA" OR "#MONA"',
            10 => '"ステラルーメン" OR "$XLM" OR "#XLM"',
            11 => '"クアンタム" OR "$QTUM" OR "#QTUM"',

        ];

        // 15分毎のリクエストの回数をカウントしていく
        $request_limit_quarter_count = 0;
        // 15分毎180回制限のリクエストをカウントしていく
        $request_total_limit = 0;
       
        // ツイートを取得する期間を設定
        // このような形式にする：since:2018-12-31_23:59:59_JST until:2019-01-01_00:00:00_JST
        $now_time = date("Y-m-d_H:i:s")."_JST";//今の時間
        // dd($now_time);

        $before_hour = $this->getPeriod($period);
        // $before_hour = date('Y-m-d_H:i:s', strtotime('-1 week', time()))."_JST";//カウント開始の時間
        dd($before_hour);

    
        // search_keyに格納した銘柄ごとにツイートを取得する
        for($i = 0; $i < count($search_key); $i++){

            // 仮想通貨銘柄に関するツイートを検索
            // consig/app.phpでエイリアスを設定しているので、useしなくてもバックスラッシュで読み込める
            // 'Twitter' => App\Facades\Twitter::class,
            $params = [
                'q' => $search_key[$i],
                'count' => 100,
                'result_type' => 'recent', // 取得するツイートの種類（recent＝最新のツイート）
                'since' => $before_hour, // 指定日時以降のツイートを取得
                'until' => $now_time // 指定日時以前のツイートを取得
            ];

            \Log::debug($search_key[$i]. ' の検索が始まっています。');
            \Log::debug('  ');

            // ツイートを取得してく
            for($k = 0; $k < self::REQUEST_LIMIT＿MINUTES; $k++){

                // リクエストの上限値に来たら処理を停止
                if($request_limit_quarter_count == self::REQUEST_LIMIT＿MINUTES){
                    \Log::debug('15分毎15回のリクエスト上限に到達しました');
                    \Log::debug('  ');
                    // リクエストをリセット
                    $request_limit_quarter_count = 0;
                    break;
                }
                
                // オブジェクト形式で返ってくる
                $result_obj = \Twitter::get('search/tweets', $params);
                // dd($result_obj);

                // リクエストをカウント
                ++$request_limit_quarter_count;
                \Log::debug('リクエスト数をカウントしています：'. $request_limit_quarter_count .' 回');
    
                // オブジェクトを配列に変換
                $result_arr = json_decode(json_encode($result_obj), true);
                    // ツイート本文を抽出
                    for($h = 0; $h < count($result_arr['statuses']); $h++){
                        
                        $tweet_text[] = $result_arr['statuses'][$h]['text'];
                    }
                    
                    // next_resultsがなければ処理を終了
                    if(empty($result_arr['search_metadata']['next_results'])){
                        \Log::debug('検索結果が空になったので次の処理へ移ります');
                        \Log::debug('  ');
                        // リクエストをリセット
                        $request_limit_quarter_count = 0;
                        break;
                    }
    
                    // パラメータの先頭の？を除去（次のページの）
                    $next_results = preg_replace('/^\?/', '', $result_arr['search_metadata']['next_results']);
                    
                    // パラメータに変換
                    parse_str($next_results, $params);

            }

            \Log::debug($search_key[$i]. ' の結果をDBへ保存します これは添字です '. $i . ' DB登録に使用する際は+1して使用して下さい');

            // 各銘柄ごとにツイート数をカウントしてDBへ保存する
            // 銘柄の集計結果
            $trend_count = count($tweet_text);
            // 添字なのでDB保存用に+1しておく
            ++$i; 
            $coinObj = $coin->find($i);
            // updateOrCreateメソッド：第一引数に指定したカラムに値が存在していれば更新し、無ければ新規登録する
            $coinObj->trends()->updateOrCreate(
                ['coin_id' => $i],
                ['week' => $trend_count]);

            // カウントしたままだと次の通貨を飛ばしてしまうのでデクリメントしておく
            --$i;
            \Log::debug('登録が完了しました。次の銘柄へ移ります');
            \Log::debug('  ');

            // カウントした値を初期化する
            $tweet_text = [];
            // リクエスト回数をリセット
            $request_limit_quarter_count = 0;
        }

        \Log::debug('ここの処理は最後かな？');
        \Log::debug('  ');

    }

    // データ取得用の日時を返す関数
    private function getPeriod($period)
    {   
        
        switch($period){
            case 'hour':
                return  $before_hour = date('Y-m-d_H:i:s', strtotime('-1 hour', time()))."_JST"; //カウント開始の時間

            case 'day':
                return $before_hour = date('Y-m-d_H:i:s', strtotime('-1 day', time()))."_JST"; //カウント開始の時間
                
            case 'week':
                return $before_hour = date('Y-m-d_H:i:s', strtotime('-1 week', time()))."_JST"; //カウント開始の時間

            default:
               \Log::debug('日時を返す関数に不正な値が入りました。');
            break;
        }

        
    }
}

