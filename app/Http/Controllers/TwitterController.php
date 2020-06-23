<?php

namespace App\Http\Controllers;

use App\TwitterUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Carbon; // ★ 追加
use Illuminate\Support\Facades\DB; // ★追加

class TwitterController extends Controller
{


    // 関連キーワードがユーザー名又はプロフィールに記載しているユーザーを取得
    public static function userList()
    {
        // 新規登録カウント
        $newCounter = 0;
        // 既存登録数のカウント
        $alreadyCounter = 0;

        \Log::debug('===== ツイート取得バッチを開始します：' . date('Y年m月d日') . '=====');
        // DBに登録されているユーザーを取得
        $TwitterUser = new TwitterUser();
        $dbresult = $TwitterUser->all();

        // 検索ワード
        $search_key = '仮想通貨';
        $search_limit_count = 20;
        $page_random = rand(1, 100);

                $options = [
            'q' => $search_key,
            'count' => $search_limit_count,
            'page' => $page_random,
            'lang' => 'ja',
            ];

        // 仮想通貨に関するユーザーを検索
        $search_result = \Twitter::get('users/search', $options);

        // dd(print_r($search_result, true));

        // DBから返却されたコレクションが空だったら初期処理として新規登録します
        if ($dbresult->isEmpty()) {
            \Log::debug('twitter_usersテーブルが空なので初期登録処理を実行します。：');
            foreach ($search_result as $search_result_item) {
                $twitter_user[] = [
                'twitter_id' => $search_result_item->id,
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
                $result = $TwitterUser->where('twitter_id', $search_user_id)->get();

                // Collectionが空でなければDBに既に登録させれいるTwitterユーザー
                if ($result->isNotEmpty()) {
                    // idで検索できていればDBに存在している
                    ++$alreadyCounter;
                    \Log::debug("DBに存在していたユーザーです。既存ユーザーカウンター：{$alreadyCounter}");
                    $twitter_user = [
                        'twitter_id' => $search_result_item->id,
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
                    $TwitterUser->where('twitter_id', $search_user_id)->update($twitter_user);
                    \Log::debug('更新しました。更新したID：' . $search_user_id);
                } else {
                    ++$newCounter;
                    \Log::debug("DBに存在していなかったユーザーです。新規ユーザーカウンター：{$newCounter}");

                    $twitter_user = [
                        'twitter_id' => $search_result_item->id,
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
            }
        }
    }

    public function index()
    {
        // 認証されたユーザー情報を取得
        $outh_user = Auth::user();
        // JSON形式へ変換
        $user = json_encode($outh_user);
        // 新しく登録されたアカウントから表示していく
        $result = TwitterUser::orderBy('id', 'desc')->get();
        // 取得した情報をJSON形式へ変換
        $tw_user = json_encode($result);

        // return view('userList', ['tw_user' => $tw_user]);
        return view('userList', compact('tw_user', 'user'));
    }



    /**
     * これはテスト用の処理
     */

    // ユーザーのAPIリミットの残存回数を取得
    public function limit()
    {
        $connect = $this->twitterAuth();

        $search_result = $connect->get('application/rate_limit_status');

        dd($search_result->resources);

       
    }

    public function twitterAuth()
    {
        // ヘルパー関数のconfigメソッドを通じて、config/services.phpのtwitterの登録した中身を参照
        $config = config('services.twitter');
        // APIキーを格納
        $api_key = $config['client_id'];
        $api_key_secret = $config['client_secret'];
        // アクセストークンを格納
        $access_token = session('access_token');
        $access_token_secret = session('access_token_secret');

        $OAuth = new TwitterOAuth($api_key, $api_key_secret, $access_token, $access_token_secret);

        return $OAuth;
    }
}
