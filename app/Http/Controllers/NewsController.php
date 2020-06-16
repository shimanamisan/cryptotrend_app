<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // ★追加

class NewsController extends Controller
{
    public function index()
    {
        Log::debug('====== Google APIからニュースを取得しています ======');
        // $keyword:キーワード検索の文言
        $keyword = '仮想通貨';
        // $mux_num:取得する記事の上限
        // $max_num = 100;

        // この処理の最大実行時間を指定する。デフォルトは30秒。
        set_time_limit(90);

        // キーワードの文字コード変更
        $query = urlencode(mb_convert_encoding($keyword, 'UTF-8', "auto"));

        // ベースとなるURL
        $API_BASE_URL =
            // ie=UTF-8で特定のブラウザでも文字化けしないように文字コードを指定している
            "https://news.google.com/rss/search?ie=UTF-8&oe=UTF-8&q=" .
            $query .
            // hl,cied,glパラメータで言語の指定を行っている
            "&hl=ja&gl=JP&ceid=JP:ja";

        // APIにアクセス
        // $contents = file_get_contents($API_BASE_URL);
        // $xml = simplexml_load_string($contents);

        $items = simplexml_load_file($API_BASE_URL)->channel->item;

        Log::debug("取得した記事の数：" . count($items));

        //記事のタイトルとURLを取り出して配列に格納
        for ($i = 0; $i < count($items); $i++) {
            $list[$i]['title'] = mb_convert_encoding(
                $items[$i]->title,
                "UTF-8",
                "auto"
            );
            // $url_split = explode("=", (string) $items[$i]->link);
            $list[$i]['url'] = mb_convert_encoding(
                $items[$i]->link,
                "UTF-8",
                "auto"
            );
            $list[$i]['pubDate'] = mb_convert_encoding(
                $items[$i]->pubDate,
                "UTF-8",
                "auto"
            );
        }

        // json形式へ変換
        $newsList = json_encode($list);
        
        // return view('news')->with('newsList', $newsList);
        return view('news', ['newsList' => $newsList]);
        // return view('news', compact('list'));
    }
}
