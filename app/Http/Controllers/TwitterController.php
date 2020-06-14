<?php

namespace App\Http\Controllers;

use App\TwitterUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ★追加
use Illuminate\Support\Carbon; // ★ 追加

class TwitterController extends Controller
{
    // デバッグ関数
    public function debug($str)
    {
        \Log::debug($str);
    }

    // Twitter上で仮想通貨関連のユーザーを取得する処理
    // バッチ処理で1日に一回更新します
    public function searchTweet()
    {
        // 検索ワード
        $search_key = '"ビットコイン" OR "BTC"';

        // 仮想通貨銘柄に関するツイートを検索
        $result = \Twitter::get('search/tweets', [
            'q' => $search_key,
            'count' => 10,
        ])->statuses;

        $this->debug('取得したツイートです：' . print_r($result, true));

        return view('twitter', ['result' => $result]);
    }

    // 関連キーワードをつぶやいているユーザーを取得
    public function userList()
    {
        // 新規登録カウント
        $newCounter = 0;
        // 既存登録数のカウント
        $alreadyCounter = 0;

        $this->debug(
            '===== ツイート取得バッチを開始します：' .
                date('Y年m月d日') .
                '====='
        );
        // DBに登録されているユーザーを取得
        $TwitterUser = new TwitterUser();
        $dbresult = $TwitterUser->all();

        // 検索ワード
        $search_key = '仮想通貨';
        $search_limit_count = 100;

        $options = [
            'q' => $search_key,
            'count' => $search_limit_count,
        ];

        // 仮想通貨に関するツイートを検索
        $search_result = \Twitter::get('search/tweets', $options)->statuses;

        // DBから返却されたコレクションが空だったら初期処理として新規登録します
        if ($dbresult->isEmpty()) {
            $this->debug(
                "twitter_usersテーブルが空なので初期登録処理を実行します。："
            );
            foreach ($search_result as $search_result_item) {
                $twitter_user[] = [
                    'twitter_id' => $search_result_item->user->id,
                    'user_name' => $search_result_item->user->name,
                    'account_name' => $search_result_item->user->screen_name,
                    'new_tweet' => $search_result_item->text,
                    'description' => $search_result_item->user->description,
                    'friends_count' => $search_result_item->user->friends_count,
                    'followers_count' =>
                        $search_result_item->user->followers_count,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            $TwitterUser->insert($twitter_user);
            $this->debug('登録が完了しました。');
        } else {
            $this->debug('2回目以降の処理です。');
            // DBから取得したCollectionを分解する
            foreach ($search_result as $search_result_item) {
                // 検索してきた結果からTwitterUserIDを取り出しています
                $search_user_id = $search_result_item->user->id;
                $this->debug(
                    'TwitterユーザーのIDを取り出しています：' . $search_user_id
                );

                // 既に登録済みのIDかDBを検索する
                $result = $TwitterUser
                    ->where('twitter_id', $search_user_id)
                    ->get();

                // Collectionが空でなければDBに既に登録させれいるTwitterユーザー
                if ($result->isNotEmpty()) {
                    // idで検索できていればDBに存在している
                    ++$alreadyCounter;
                    $this->debug(
                        "DBに存在していたユーザーです。既存ユーザーカウンター：{$alreadyCounter}"
                    );
                    $twitter_user = [
                        'twitter_id' => $search_result_item->user->id,
                        'user_name' => $search_result_item->user->name,
                        'account_name' =>
                            $search_result_item->user->screen_name,
                        'new_tweet' => $search_result_item->text,
                        'description' => $search_result_item->user->description,
                        'friends_count' =>
                            $search_result_item->user->friends_count,
                        'followers_count' =>
                            $search_result_item->user->followers_count,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                    // 存在していたユーザーの情報を更新します
                    $TwitterUser
                        ->where('twitter_id', $search_user_id)
                        ->update($twitter_user);
                    $this->debug(
                        '更新しました。更新したID：' . $search_user_id
                    );
                    $this->debug(
                        '更新した内容：' . print_r($twitter_user, true)
                    );
                } else {
                    ++$newCounter;
                    $this->debug(
                        "DBに存在していなかったユーザーです。新規ユーザーカウンター：{$newCounter}"
                    );

                    $twitter_user = [
                        'twitter_id' => $search_result_item->user->id,
                        'user_name' => $search_result_item->user->name,
                        'account_name' =>
                            $search_result_item->user->screen_name,
                        'new_tweet' => $search_result_item->text,
                        'description' => $search_result_item->user->description,
                        'friends_count' =>
                            $search_result_item->user->friends_count,
                        'followers_count' =>
                            $search_result_item->user->followers_count,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];

                    $TwitterUser->insert($twitter_user);
                    $this->debug('新規登録しました。');
                }
            }
        }
    }

    public function index()
    {
        $user_list = TwitterUser::all();

        $tw_user = json_decode($user_list);

        return view('userList', ['user_list' => $user_list]);
    }
}
