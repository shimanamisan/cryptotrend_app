<?php

namespace App\Console\Commands;

use App\Twuser;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetTwitterUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:gettwitterusers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TwitterAPIを利用して関連キーワードを呟いているユーザーを取得します';

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
        // 関連キーワードがユーザー名又はプロフィールに記載しているユーザーを取得

        // 新規登録カウント
        $newCounter = 0;
        // 既存登録数のカウント
        $alreadyCounter = 0;

        \Log::debug('===== ツイート取得バッチを開始します：' . date('Y年m月d日') . '=====');
        // DBに登録されているユーザーを取得
        $TwitterUser = new Twuser();
        $dbresult = $TwitterUser->all();

        // 検索ワード
        $search_key = '仮想通貨';
        $search_limit_count = 20;
        $page_random = rand(1, 10);

        $options = [
            'q' => $search_key,
            'count' => $search_limit_count,
            'page' => $page_random,
            'lang' => 'ja',
            ];

        // 仮想通貨に関するユーザーを検索
        $search_result = \Twitter::get('users/search', $options);
        // DBから返却されたコレクションが空だったら初期処理として新規登録します
        if ($dbresult->isEmpty()) {
            \Log::debug('twitter_usersテーブルが空なので初期登録処理を実行します。：');
            foreach ($search_result as $search_result_item) {
              
                $twitter_user[] = [
                'id' => $search_result_item->id,
                'user_name' => $search_result_item->name,
                'account_name' => $search_result_item->screen_name,
                'new_tweet' => $search_result_item->status->text,
                'description' => $search_result_item->description,
                'friends_count' => $search_result_item->friends_count,
                'followers_count' => $search_result_item->followers_count,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                ];
            }
            $TwitterUser->insert($twitter_user);
            \Log::debug('登録が完了しました。');
        } else {
            \Log::debug('2回目以降の処理です。');
            // DBから取得したCollectionを分解する
            foreach ($search_result as $search_result_item) {
                // 検索してきた結果からTwitterUserIDを取り出しています
                $search_user_id = $search_result_item->id;
                \Log::debug('TwitterユーザーのIDを取り出しています：' . $search_user_id);

                // 既に登録済みのIDかDBを検索する
                $result = $TwitterUser->where('id', $search_user_id)->get();

                try {
                    // Collectionが空でなければDBに既に登録させれいるTwitterユーザー
                    if ($result->isNotEmpty()) {
                        // idで検索できていればDBに存在している
                        ++$alreadyCounter;
                        \Log::debug("DBに存在していたユーザーです。既存ユーザーカウンター：{$alreadyCounter}");
                        $twitter_user = [
                        'id' => $search_result_item->id,
                        'user_name' => $search_result_item->name,
                        'account_name' => $search_result_item->screen_name,
                        'new_tweet' => $search_result_item->status->text,
                        'description' => $search_result_item->description,
                        'friends_count' => $search_result_item->friends_count,
                        'followers_count' => $search_result_item->followers_count,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                        // 存在していたユーザーの情報を更新します
                        $TwitterUser->where('id', $search_user_id)->update($twitter_user);
                        \Log::debug('更新しました。更新したID：' . $search_user_id);
                    } else {
                        ++$newCounter;
                        \Log::debug("DBに存在していなかったユーザーです。新規ユーザーカウンター：{$newCounter}");

                        $twitter_user = [
                        'id' => $search_result_item->id,
                        'user_name' => $search_result_item->name,
                        'account_name' => $search_result_item->screen_name,
                        'new_tweet' => $search_result_item->status->text,
                        'description' => $search_result_item->description,
                        'friends_count' => $search_result_item->friends_count,
                        'followers_count' => $search_result_item->followers_count,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                        $TwitterUser->insert($twitter_user);
                        \Log::debug('新規登録しました。' . print_r($twitter_user, true));
                    }
                } catch (\Exception $e) {
                    \Log::debug('例外が発生しました。ループ処理をスキップします' .$e->getMessage());
                    continue;
                }
            }
        }
    }
}
