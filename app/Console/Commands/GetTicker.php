<?php

namespace App\Console\Commands;

use App\Coin; // ★追記
use Illuminate\Console\Command;

class GetTicker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "command:getticker";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "CoincheckAPIから24時間の取引価格を取得します";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    const BCT = "BTC（ビットコイン）";
    const BCH = "BCH（ビットコインキャッシュ）";
    const ETH = "ETH（イーサリアム）";
    const MONA = "MONA（モナコイン）";
    const XEM = "XEM（ネム）";
    const XRP = "XRP（リップル）";
    const LTC = "LTC（ライトコイン）";

    public function handle(Coin $coin)
    {
        \Log::debug("取引価格を取得するバッチ処理が実行されています");
        \Log::debug("    ");

        /****************************************
         zaif_apikから価格情報を取得する
        *****************************************/
        // ticker api url
        $zaif_api_url = "https://api.zaif.jp/api/1/ticker/";

        // ticker api pram
        $currency_pairs = [
            "BCH（ビットコインキャッシュ）" => "bch_jpy", // ビットコインキャッシュ
            "ETH（イーサリアム）" => "eth_jpy", // イーサリアム
            "MONA（モナコイン）" => "mona_jpy", // モナコイン
            "XEM（ネム）" => "xem_jpy", // ネム
        ];

        // curl option
        $option = [
            CURLOPT_RETURNTRANSFER => true, //文字列として返す
            CURLOPT_TIMEOUT => 3, // タイムアウト時間
        ];

        foreach ($currency_pairs as $currency_kye => $currency) {
            $url = $zaif_api_url . $currency;

            \Log::debug("セットしたURLです。：" . $url);
            \Log::debug("    ");
            \Log::debug("キーを出力しています。" . $currency_kye);
            \Log::debug("    ");

            $curl = curl_init($url);
            // curl_setopt_arrayメソッドでCURL転送用の複数のオプションを設定する
            curl_setopt_array($curl, $option);

            $json = curl_exec($curl); // cURL セッションを実行する https://www.php.net/manual/ja/function.curl-exec.php
            $info = curl_getinfo($curl); // 指定した伝送に関する情報を得る https://www.php.net/manual/ja/function.curl-getinfo.php
            $error_number = curl_errno($curl); // 直近のエラー番号を返す https://www.php.net/manual/ja/function.curl-errno.php

            // OK以外はエラーなので空白を返す
            if ($error_number !== CURLE_OK) {
                // 更に詳細にエラーハンドリングする場合はエラー番号で判定
                // タイムアウトの場合はCURLE_OPERATION_TIMEDOUT
                return [];
            }

            // 200以外のステータスコードは失敗とみなし空配列を返す
            if ($info["http_code"] !== 200) {
                return [];
            }

            // 文字列から変換
            $jsonArray = json_decode($json, true);
            \Log::debug(
                "取得したJSON形式の情報を配列に変換しています。：" .
                    print_r($jsonArray, true)
            );
            \Log::debug("    ");

            // 価格を取り出す
            $max_price = $jsonArray["high"];
            $low_price = $jsonArray["low"];

            // 過去24時間の高値を取り出してDBへ保存する
            \Log::debug(
                "各銘柄の過去24時間の最高取引価格を取り出しています。" .
                    $max_price
            );
            \Log::debug("    ");
            \Log::debug(
                "各銘柄の過去24時間の最安取引価格を取り出しています。" .
                    $low_price
            );
            \Log::debug("    ");
            try {
                $coin_data = $coin->where("coin_name", $currency_kye)->first();
                $coin_data->max_price = $max_price;
                $coin_data->low_price = $low_price;
                $coin_data->save();
            } catch (\Exception $e) {
                \Log::debug(
                    "例外が発生しました。処理を停止します。" . $e->getMessage()
                );
            }
        }

        /****************************************
         coincheck_apikから価格情報を取得する
        *****************************************/
        // 現在はビットコインのみ24時間の取引価格を取得
        $coincheck_api_url = "https://coincheck.com/api/ticker";

        // curl option
        $option = [
            CURLOPT_RETURNTRANSFER => true, //文字列として返す
            CURLOPT_TIMEOUT => 3, // タイムアウト時間
        ];

        $curl = curl_init($coincheck_api_url);
        // curl_setopt_arrayメソッドでCURL転送用の複数のオプションを設定する
        curl_setopt_array($curl, $option);

        $json = curl_exec($curl); // cURL セッションを実行する https://www.php.net/manual/ja/function.curl-exec.php
        $info = curl_getinfo($curl); // 指定した伝送に関する情報を得る https://www.php.net/manual/ja/function.curl-getinfo.php
        $error_number = curl_errno($curl); // 直近のエラー番号を返す https://www.php.net/manual/ja/function.curl-errno.php

        // OK以外はエラーなので空白を返す
        if ($error_number !== CURLE_OK) {
            // 更に詳細にエラーハンドリングする場合はエラー番号で判定
            // タイムアウトの場合はCURLE_OPERATION_TIMEDOUT
            \Log::debug("エラーが発生しました。エラー番号：" . $error_number);
            \Log::debug("    ");
            return [];
        }

        // 200以外のステータスコードは失敗とみなし空配列を返す
        if ($info["http_code"] !== 200) {
            \Log::debug("HTTP通信エラーが発生しました。");
            \Log::debug("    ");
            return [];
        }

        // 文字列から変換
        $jsonArray = json_decode($json, true);
        \Log::debug(
            "コインチェックAPIから取得したJSON形式の情報を配列に変換しています。：" .
                print_r($jsonArray, true)
        );
        \Log::debug("    " . self::BCT);

        // 価格を取り出す
        $max_price = $jsonArray["high"];
        $low_price = $jsonArray["low"];

        try {
            $coin_data = $coin->where("coin_name", self::BCT)->first();
            $coin_data->max_price = $max_price;
            $coin_data->low_price = $low_price;
            $coin_data->save();
        } catch (\Exception $e) {
            \Log::debug(
                "例外が発生しました。処理を停止します。" . $e->getMessage()
            );
            \Log::debug("    ");
        }
        /****************************************
         bitbank_apikから価格情報を取得する
        *****************************************/
        $pair_list = [
            "xrp_jpy", // リップル
            "ltc_jpy", // ライトコイン
        ];
        foreach ($pair_list as $pair) {
            $bitbank_api_url = "https://public.bitbank.cc/{$pair}/ticker";

            // curl option
            $option = [
                CURLOPT_RETURNTRANSFER => true, //文字列として返す
                CURLOPT_TIMEOUT => 3, // タイムアウト時間
            ];

            $curl = curl_init($bitbank_api_url);
            // curl_setopt_arrayメソッドでCURL転送用の複数のオプションを設定する
            curl_setopt_array($curl, $option);

            $json = curl_exec($curl); // cURL セッションを実行する https://www.php.net/manual/ja/function.curl-exec.php
            $info = curl_getinfo($curl); // 指定した伝送に関する情報を得る https://www.php.net/manual/ja/function.curl-getinfo.php
            $error_number = curl_errno($curl); // 直近のエラー番号を返す https://www.php.net/manual/ja/function.curl-errno.php

            // OK以外はエラーなので空白を返す
            if ($error_number !== CURLE_OK) {
                // 更に詳細にエラーハンドリングする場合はエラー番号で判定
                // タイムアウトの場合はCURLE_OPERATION_TIMEDOUT
                \Log::debug(
                    "エラーが発生しました。エラー番号：" . $error_number
                );
                \Log::debug("    ");
                return [];
            }

            // 200以外のステータスコードは失敗とみなし空配列を返す
            if ($info["http_code"] !== 200) {
                \Log::debug("HTTP通信エラーが発生しました。");
                \Log::debug("    ");
                return [];
            }

            // 文字列から変換
            $jsonArray = json_decode($json, true);
            \Log::debug(
                "ビットバンクAPIから取得したJSON形式の情報を配列に変換しています。：" .
                    print_r($jsonArray, true)
            );
            \Log::debug("    ");

            // 価格を取り出す
            $max_price = $jsonArray["data"]["high"];
            $low_price = $jsonArray["data"]["low"];

            try {
                $coin_data = $coin->where("coin_name", self::XRP)->first();
                $coin_data->max_price = $max_price;
                $coin_data->low_price = $low_price;
                $coin_data->save();

                $coin_data = $coin->where("coin_name", self::LTC)->first();
                $coin_data->max_price = $max_price;
                $coin_data->low_price = $low_price;
                $coin_data->save();
            } catch (\Exception $e) {
                \Log::debug(
                    "例外が発生しました。処理を停止します。" . $e->getMessage()
                );
                \Log::debug("    ");
            }
        }
    }
}
