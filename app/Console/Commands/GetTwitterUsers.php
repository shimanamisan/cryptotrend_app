<?php

namespace App\Console\Commands;

use App\Twuser;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Abraham\TwitterOAuth\TwitterOAuth; // 追加

class GetTwitterUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "command:gettwitterusers";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "TwitterAPIを利用して関連キーワードを呟いているユーザーを取得します";

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
    public function handle()
    {
        \Log::debug("====================================================");
        \Log::debug("関連キーワードを呟いているユーザーを取得 : 開始");
        \Log::debug("====================================================");

        // インスタンスを生成
        $connection = $this->twitterOauth();
        // 新規登録カウント
        $newCounter = 0;
        // 既存登録数のカウント
        $alreadyCounter = 0;
        // DBに登録されているユーザーを取得
        $TwitterUser = new Twuser();
        $dbresult = $TwitterUser->all();

        // 検索ワード
        $search_key = "仮想通貨";
        $search_limit_count = 20;
        $page_random = [];
        // ページング用の値を1から20まで配列に格納
        for ($i = 1; $i <= 20; $i++) {
            $page_random[] = $i;
        }

        // 配列の個数分ループしてユーザーを取得する
        foreach ($page_random as $page) {
            \Log::debug("=============================");
            \Log::debug($page . "ページ目を取得しています。");
            \Log::debug("=============================");

            $options = [
                "q" => $search_key,
                "page" => $page,
                "count" => $search_limit_count,
                "lang" => "ja",
            ];

            // 仮想通貨に関するユーザーを検索
            $search_result = $connection->get("users/search", $options);
            // DBから返却されたコレクションが空だったら初期処理として新規登録します
            if ($dbresult->isEmpty()) {
                \Log::debug(
                    "twitter_usersテーブルが空なので初期登録処理を実行します。："
                );
                \Log::debug("    ");
                foreach ($search_result as $search_result_item) {
                    $twitter_user[] = [
                        "id" => $search_result_item->id,
                        "user_name" => $search_result_item->name,
                        "account_name" => $search_result_item->screen_name,
                        "new_tweet" => $search_result_item->status->text,
                        "description" => $search_result_item->description,
                        "friends_count" => $search_result_item->friends_count,
                        "followers_count" =>
                            $search_result_item->followers_count,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ];
                }
                try {
                    $TwitterUser->insert($twitter_user);
                    \Log::debug("登録が完了しました。");
                    \Log::debug("    ");
                } catch (\Exception $e) {
                    \Log::debug(
                        "例外が発生しました。ループ処理をスキップします" .
                            $e->getMessage()
                    );
                    \Log::debug("    ");
                    continue;
                }
            } else {
                \Log::debug("2回目以降の処理です。");
                \Log::debug("    ");
                // DBから取得したCollectionを分解する
                foreach ($search_result as $search_result_item) {
                    // 検索してきた結果からTwitterUserIDを取り出しています
                    $search_user_id = $search_result_item->id;
                    \Log::debug(
                        "TwitterユーザーのIDを取り出しています：" .
                            $search_user_id
                    );
                    \Log::debug("    ");

                    // 既に登録済みのIDかDBを検索する
                    $result = $TwitterUser->where("id", $search_user_id)->get();

                    try {
                        // Collectionが空でなければDBに既に登録させているTwitterユーザー
                        if ($result->isNotEmpty()) {
                            // idで検索できていればDBに存在している
                            ++$alreadyCounter;
                            \Log::debug(
                                "DBに存在していたユーザーです。既存ユーザーカウンター：{$alreadyCounter}"
                            );
                            \Log::debug("    ");

                            // description が191文字を超える場合はDBへ登録出来ないので、文字数をカウントして超えていた場合は
                            // 文字数をカットする
                            $text = $search_result_item->description;
                            $text_count = mb_strlen($text);
                            \Log::debug(
                                "descriptionの文字数。文字数：{$text_count}"
                            );
                            \Log::debug("    ");

                            if ($text_count >= 191) {
                                \Log::debug(
                                    "descriptionの文字数が191文字を超えています。文字列を切り取ります。文字数：{$text_count}"
                                );
                                \Log::debug("    ");
                                $newDescription = mb_substr($text, 0, 190);

                                $after_text = mb_strlen($newDescription);
                                \Log::debug(
                                    "切り取り後の文字数です：{$after_text}"
                                );
                                \Log::debug("    ");

                                $twitter_user = [
                                    "id" => $search_result_item->id,
                                    "user_name" => $search_result_item->name,
                                    "account_name" =>
                                        $search_result_item->screen_name,
                                    "new_tweet" =>
                                        $search_result_item->status->text,
                                    "description" => $newDescription,
                                    "friends_count" =>
                                        $search_result_item->friends_count,
                                    "followers_count" =>
                                        $search_result_item->followers_count,
                                    "created_at" => Carbon::now(),
                                    "updated_at" => Carbon::now(),
                                ];
                            } else {
                                \Log::debug(
                                    "descriptionの文字数は超えていません。"
                                );
                                \Log::debug("    ");
                                $twitter_user = [
                                    "id" => $search_result_item->id,
                                    "user_name" => $search_result_item->name,
                                    "account_name" =>
                                        $search_result_item->screen_name,
                                    "new_tweet" =>
                                        $search_result_item->status->text,
                                    "description" =>
                                        $search_result_item->description,
                                    "friends_count" =>
                                        $search_result_item->friends_count,
                                    "followers_count" =>
                                        $search_result_item->followers_count,
                                    "created_at" => Carbon::now(),
                                    "updated_at" => Carbon::now(),
                                ];
                            }

                            // 存在していたユーザーの情報を更新します
                            $TwitterUser
                                ->where("id", $search_user_id)
                                ->update($twitter_user);
                            \Log::debug(
                                "更新しました。更新したID：" . $search_user_id
                            );
                            \Log::debug("    ");
                        } else {
                            ++$newCounter;
                            \Log::debug(
                                "DBに存在していなかったユーザーです。新規ユーザーカウンター：{$newCounter}"
                            );
                            \Log::debug("    ");

                            // description が191文字を超える場合はDBへ登録出来ないので、文字数をカウントして超えていた場合は
                            // 文字数をカットする
                            $text = $search_result_item->description;
                            $text_count = mb_strlen($text);
                            \Log::debug(
                                "descriptionの文字数。文字数：{$text_count}"
                            );
                            \Log::debug("    ");

                            if ($text_count >= 191) {
                                \Log::debug(
                                    "descriptionの文字数が191文字を超えています。文字列を切り取ります。文字数：{$text_count}"
                                );
                                \Log::debug("    ");
                                $newDescription = mb_substr($text, 0, 190);

                                $after_text = mb_strlen($newDescription);
                                \Log::debug(
                                    "切り取り後の文字数です：{$after_text}"
                                );
                                \Log::debug("    ");

                                $twitter_user = [
                                    "id" => $search_result_item->id,
                                    "user_name" => $search_result_item->name,
                                    "account_name" =>
                                        $search_result_item->screen_name,
                                    "new_tweet" =>
                                        $search_result_item->status->text,
                                    "description" => $newDescription,
                                    "friends_count" =>
                                        $search_result_item->friends_count,
                                    "followers_count" =>
                                        $search_result_item->followers_count,
                                    "created_at" => Carbon::now(),
                                    "updated_at" => Carbon::now(),
                                ];
                            } else {
                                \Log::debug(
                                    "descriptionの文字数は超えていません。"
                                );
                                \Log::debug("    ");
                                $twitter_user = [
                                    "id" => $search_result_item->id,
                                    "user_name" => $search_result_item->name,
                                    "account_name" =>
                                        $search_result_item->screen_name,
                                    "new_tweet" =>
                                        $search_result_item->status->text,
                                    "description" =>
                                        $search_result_item->description,
                                    "friends_count" =>
                                        $search_result_item->friends_count,
                                    "followers_count" =>
                                        $search_result_item->followers_count,
                                    "created_at" => Carbon::now(),
                                    "updated_at" => Carbon::now(),
                                ];
                            }

                            $TwitterUser->insert($twitter_user);
                            \Log::debug(
                                "新規登録しました。新規登録したID：" .
                                    $search_user_id
                            );
                            \Log::debug("    ");
                        }
                    } catch (\Exception $e) {
                        \Log::debug(
                            "例外が発生しました。ループ処理をスキップします" .
                                $e->getMessage()
                        );
                        \Log::debug("    ");
                        continue;
                    }
                }
            }
        }

        \Log::debug("=============================");
        \Log::debug("終了");
        \Log::debug("=============================");
    }

    public function twitterOauth()
    {
        \Log::debug("=== インスタンスを生成します === ");

        // ヘルパー関数のconfigメソッドを通じて、config/services.phpのtwitterトークン用の設定を参照
        $config = config("services.twitter");

        $api_key = $config["client_id"];
        $api_key_secret = $config["client_secret"];
        $access_token = $config["access_token"];
        $access_token_secret = $config["access_token_secret"];
        // インスタンスを生成
        $connection = new TwitterOAuth(
            $api_key,
            $api_key_secret,
            $access_token,
            $access_token_secret
        );

        return $connection;
    }
}
