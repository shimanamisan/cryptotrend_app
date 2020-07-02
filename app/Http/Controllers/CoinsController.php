<?php

namespace App\Http\Controllers;

use App\Coin; // ★追記
use App\Trend; // ★追記
use Carbon\Carbon; // ★追記
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Abraham\TwitterOAuth\TwitterOAuth; // ★追記
use Abraham\TwitterOAuth\TwitterOAuthException; // ★追記
use Laravel\Socialite\Facades\Socialite; // 追加


class CoinsController extends Controller
{
    // search/tweetsのリクエストの上限は、15分毎450回（アプリケーション認証時）
    const SEARCH_REQUEST_LIMIT = 450;
    // 
    const REQUEST_LIMIT＿MINUTES = 15;

    public function index()
    {
        // ビューファイルを表示させる
        return view('coins');
    }

    public function getTrendCoins()
    {
        $coins = DB::table('coins')
        ->join('trends', 'coins.id', '=', 'trends.coin_id')
        ->orderBy('week', 'DESC')
        ->get();

        return $coins;
    }


    // Twitter上で仮想通貨関連のツイートをしているユーザーを取得する処理
    // 30分毎にバッチ処理で定期的に実行
    public function getTrendTweet(Coin $coin, $date)
    {   
   
        \Log::debug($date . ' の処理が開始しています');
        \Log::debug('   ');
   
        // 検索ワード（ハッシュタグまで検索に含める）
        $search_key = [
            0 => '"ビットコイン" OR "BTC"',
            1 => '"イーサリアム" OR "ETH"',
            2 => '"イーサリアムクラシック" OR "ETC"',
            3 => '"リスク" OR "LSK"',
            4 => '"ファクトム" OR "FCT"',
            5 => '"リップル" OR "XRP"',
            6 => '"ネム" OR "XEM"',
            7 => '"ライトコイン" OR "LTC"',
            8 => '"ビットコインキャッシュ" OR "BCH"',
            9 => '"モナコイン" OR "MONA"',
            10 => '"ステラルーメン" OR "XLM"',
            11 => '"クアンタム" OR "QTUM"',
        ];
        // $search_key = [
        //     0 => '"ビットコイン" OR "$BTC" OR "#BTC"',
        //     1 => '"イーサリアム" OR "$ETH" OR "#ETH"',
        //     2 => '"イーサリアムクラシック" OR "$ETC" OR "#ETC"',
        //     3 => '"リスク" OR "$LSK" OR "#LSK"',
        //     4 => '"ファクトム" OR "$FCT" OR "#FCT"',
        //     5 => '"リップル" OR "$XRP" OR "#XRP"',
        //     6 => '"ネム" OR "$XEM" OR "#XEM"',
        //     7 => '"ライトコイン" OR "$LTC" OR "#LTC"',
        //     8 => '"ビットコインキャッシュ" OR "$BCH" OR "#BCH"',
        //     9 => '"モナコイン" OR "$MONA" OR "#MONA"',
        //     10 => '"ステラルーメン" OR "$XLM" OR "#XLM"',
        //     11 => '"クアンタム" OR "$QTUM" OR "#QTUM"',
        // ];

        // 15分毎450回制限のリクエストをカウントしていく
        $search_request_limit_count = 0;
        // 15分毎のリクエストの回数をカウントしていく
        $request_limit_quarter_count = 0;

        // ツイートを取得する期間を設定
        // このような形式にする：since:2018-12-31_23:59:59_JST until:2019-01-01_00:00:00_JST
        $now_time = date("Y-m-d_H:i:s")."_JST";//今の時間
        // 検索開始日時を取得
        $search_date = $this->getSearchDate($date);
       
        \Log::debug($search_date. ' ：この日時より現在の日時に向かってツイートを取得します');
        \Log::debug('  ');

        // search_keyに格納した銘柄ごとにツイートを取得する
        for($i = 0; $i < count($search_key); $i++){
            
            $params = [
                'q' => $search_key[$i],
                'count' => 100,
                'result_type' => 'recent', // 取得するツイートの種類（recent＝最新のツイート）
                'since' => $search_date, // 指定日時以降のツイートを取得
                'until' => $now_time, // 指定日時より過去のツイートを取得
                'lang' => 'ja'
            ];

            \Log::debug($search_key[$i]. ' の検索が始まっています。');
            \Log::debug('  ');

            // ツイートを取得してく（450回の制限を超えないように）
            for($k = 0; $k < self::SEARCH_REQUEST_LIMIT; $k++){
            // for($k = 0; $k < self::SEARCH_REQUEST_LIMIT; $k++){
            
                try{
                    // アプリケーション認証
                    $connection = $this->twitterOauth2();
                    // オブジェクト形式で返ってくる
                    $response_result = $connection->get('search/tweets', $params);
                    
                }catch(TwitterOAuthException $e){
                    \Log::debug('============ 例外が発生しました ============');
                    \Log::debug($e->getMessage());
                    \Log::debug($e->getTrace());
                    \Log::debug('============ メッセージ終了 ============');
                    // httpリクエストに関する例外が発生した場合は、ループを抜けてして次の仮想通貨へ移る
                    break;
                }

                // エラーハンドリング
                if($connection->getLastHttpCode() !== 200){
                    // サーバ側でエラーが発生若しくはAPI制限がかかったら処理を停止する
                    // 15分間待機して処理を継続する
                    \Log::debug('サーバ側でエラー若しくはAPI制限に掛かりました。処理を待機します');
                    \Log::debug('900秒待機中・・・・');
                    // 900秒（15分間待機して処理を再開する）
                    sleep(900);
                    continue;
                }
                    // リクエストをカウント
                    ++$search_request_limit_count;
                    \Log::debug('リクエスト数をカウントしています：'. $search_request_limit_count .' 回');
    
                    // オブジェクトを配列に変換
                    // $result_arr = (array)$response_result;
                    $result_arr = json_decode(json_encode($response_result), true);
    
                        // ツイート本文を抽出
                        for($h = 0; $h < count($result_arr['statuses']); $h++){
                            
                            $tweet_text[] = $result_arr['statuses'][$h]['text'];
                        }
                        
                        // next_resultsがなければ処理を終了
                        if(empty($result_arr['search_metadata']['next_results'])){
                            \Log::debug('検索結果が空になったので次の処理へ移ります');
                            \Log::debug('  ');
                            // リクエストをリセット
                            $search_request_limit_count = 0;
                            break;
                        }
        
                        // パラメータの先頭の？を除去（次のページの）
                        $next_results = preg_replace('/^\?/', '', $result_arr['search_metadata']['next_results']);

                        \Log::debug($next_results);
                        
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
            
            // カウントしたツイートをDBへ保存
            // 1時間、1日、1週間のツイート取得で処理を分ける
            $this->saveTrends($coinObj, $i, $trend_count ,$date);

            // カウントしたままだと次の通貨を飛ばしてしまうのでデクリメントしておく
            --$i;
            \Log::debug('登録が完了しました。次の銘柄へ移ります');
            \Log::debug('  ');

            // カウントした値を初期化する
            $tweet_text = [];
            // リクエスト回数をリセット
            $search_request_limit_count = 0;
        }

        \Log::debug('ここの処理は最後かな？');
        \Log::debug('  ');

    }

    // アプリケーション単位で認証する（ベアラートークンの取得）
    private function twitterOauth2()
    {
        // ヘルパー関数のconfigメソッドを通じて、config/services.phpのtwitterトークン用の設定を参照
        $config = config('services.twitter');
        // アプリ認証参考
        // https://qiita.com/yasunori_tanochi_gp/items/2e238638f846a1b1240f
        $api_key = $config['client_id'];
        $api_key_secret = $config['client_secret'];
        $access_token = $config['access_token'];
        $access_token_secret = $config['access_token_secret'];
        // インスタンスを生成
        $connection  = new TwitterOAuth($api_key, $api_key_secret, $access_token, $access_token_secret);

        // アプリ認証用のベアラートークンを取得
        $_bearer_token = $connection->oauth2('oauth2/token', array('grant_type' => 'client_credentials'));

        // ベアラートークンをセット
        if(isset($_bearer_token->access_token)){
            $connection->setBearer($_bearer_token->access_token);
        }

        return $connection;
    }

    // データ保存時の処理を開始日時で分ける
    private function saveTrends($coinObj, $i, $trend_count ,$date)
    {
        // hour か day か week が入ってくる。それによりDBへ保存する処理を分ける
        switch($date){

            case 'hour':
                // updateOrCreateメソッド：第一引数に指定したカラムに値が存在していれば更新し、無ければ新規登録する
                $coinObj->trends()->updateOrCreate(
                    ['coin_id' => $i],
                    ['hour' => $trend_count]);
                    \Log::debug('1時間あたりのツイート数を計測したデータを保存しました');
                    \Log::debug('  ');
                break ;

            case 'day':
                $coinObj->trends()->updateOrCreate(
                    ['coin_id' => $i],
                    ['day' => $trend_count]);
                    \Log::debug('1日あたりのツイート数を計測したデータを保存しました');
                    \Log::debug('  ');
                break;
                
            case 'week':
                $coinObj->trends()->updateOrCreate(
                    ['coin_id' => $i],
                    ['week' => $trend_count]);
                    \Log::debug('1週間あたりのツイート数を計測したデータを保存しました');
                    \Log::debug('  ');
                break ;

            default:
               \Log::debug('不正な引数が入った為、処理を行いませんでした');
            break;
        }
    }

    // データ取得用の日時を返す関数
    private function getSearchDate($date)
    {   
        
        switch($date){
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

