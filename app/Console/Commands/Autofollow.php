<?php

namespace App\Console\Commands;

use App\User;
use App\Follow;
use App\Twuser;
use Carbon\Carbon;
use App\SystemManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Abraham\TwitterOAuth\TwitterOAuth;

class Autofollow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:autofollow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自動フォローを開始するコマンドです';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    // 自動フォローのステータス
    const AUTO_FOLLOW_STATUS_RUN = 1;
    const AUTO_FOLLOW_STATUS_STOP = 0;
    // 1日のフォロー上限の400を超えないように制御する（400に到達しないように、手前の値を設定）
    const DAY_FOLLOW_LIMIT = 395;
    // 1日にアプリ全体としてのフォロー制限を超えないように制御する
    const SYSTEM_FOLLOW_LIMIT = 990;
    // 15人/15分を超えないようにする為の定数
    const AUTO_FOLLOW_QUARTER_LIMIT = 14;
  
    // DBからTwitterの情報を取得してきて自分のフォロワーと見比べてフォローしていなかったら新規でフォローする
    // リクエスト/24時間、ユーザーあたり400：アプリごとに1000となっている
    // フォローの上限数は15分毎日14とする
    // 1日に1000以上フォローしなようにする

    // 自動フォロー機能呼び出し
    public function handle()
    {
        \Log::debug('=====================================================================');
        \Log::debug('AutoFollow : 開始');
        \Log::debug('=====================================================================');

        // DBからautofollowカラムが1且つ退会済みでないユーザーを取得
        // getメソッドで取得しているので返り値はコレクションクラスが返ってくる
        $user = User::where('autofollow_status', self::AUTO_FOLLOW_STATUS_RUN)->where('delete_flg', 0)->get();
        \Log::debug('=== 自動フォローステータスがONのユーザーを取得しています:handlメソッド ===');
        \Log::debug('    ');
        \Log::debug(count($user). ' 人のユーザーが自動フォローをONにしています。');
        \Log::debug('    ');

        // コレクションクラウスの空の判定はisEmptyメソッドを使う
        if ($user->isEmpty()) {
            \Log::debug('自動フォローのユーザーが存在していないため処理を終了します');
            \Log::debug('    ');
            exit();
        }
  
        /************************************************
        システムとしてのリクエスト制限に関するデータ
        *************************************************/
        $SystemManager = SystemManager::where('id', 1)->first();
        // アプリ全体としてのフォロー制限のカウント数
        $one_day_system_counter = $SystemManager->one_day_system_counter;
        // アプリ全体としてフォロー制限に関する時刻
        $system_follow_release_time = $SystemManager->one_day_system_follow_release_time;

        /****************************************
         * 各ユーザーに対するループ処理
        *****************************************/
        foreach ($user as $auto_follow_run_user_item) {
            $user = $auto_follow_run_user_item; // 各ユーザーのUserモデル
            $twitter_id = $auto_follow_run_user_item->my_twitter_id;
            $twitter_user_token = $auto_follow_run_user_item->twitter_token;
            $twitter_user_token_secret = $auto_follow_run_user_item->twitter_token_secret;
            $day_follow_limit_count = $auto_follow_run_user_item->day_follow_limit_count;

            \Log::debug('   ');
            \Log::debug('現在、' . $auto_follow_run_user_item->name. ' さんの自動フォロー処理中です');
            \Log::debug('   ');

            /************************************************
            ユーザー単位としてのリクエスト制限に関するデータ
            *************************************************/
            // 1日のフォロー上限を超えないように、現在のフォローした数を取得（上限395/日）
            $day_follow_limit_count = $user->day_follow_limit_count;
            // ユーザー個別の1日のフォローリクエスト制限解除時刻
            $day_follow_release_time = $user->day_follow_release_time;

            /******************************************************************
            15フォロー/15分を超えないようにするためのリクエスト制限に関するデータ
            *******************************************************************/
            // リクエスト制限解除時刻を格納
            $day_follow_quarter_release_time = $user->day_follow_quarter_release_time;
            // 15フォロー/15分のリクエスト制限の上限をカウントする
            $day_follow_quarter_limit_count = $user->day_follow_quarter_limit_count;

            // 制限にかかっていなければ処理を実行する
            // インスタンスを生成
            $connection = $this->autoFollowAuth($twitter_user_token, $twitter_user_token_secret);
            // DBに登録されているユーザを取得
            $twitterUserList = $this->getTwitterUser();
            // フォローしているユーザーを取得
            $follow_list = $this->fetchFollowTarget($twitter_id, $connection);
            // DBに登録されている仮想通貨関連のアカウントが空だったら以降の処理は行わない
            if (empty($twitterUserList)) {
                \Log::debug('DBに登録している仮想通貨関連のアカウントがありませんでした');
                \Log::debug('   ');
                break;
            }
            // フォローしているユーザーのIDとDBに登録されているIDの差分を取得する。一致していないもの（フォローしていないユーザー）を取得する
            // 第一引数が比較元の配列、第二引数に比較する配列を指定する
            // 比較元の配列を比較対象の配列と比較し、比較元の配列にしかない値のみを取得
            // 配列を比較して重複していない値のみ出力（第二引数の配列の値と一致したものは除外される）
            $follow_target_list = array_diff($twitterUserList, $follow_list);

            /*********************************************
             * ここから自動フォローに関するループ処理
            **********************************************/
            // DBに登録されているアカウントとユーザーが既にフォローしているアカウントを比較した配列を順番にループしていく
            foreach ($follow_target_list as $follow_target_id) {

                // 現在の時間を格納
                $now_time = Carbon::now();
            
                // 全てフォローしてリストが空だったら処理を停止
                if (empty($follow_target_list)) {
                    \Log::debug('フォローリストが空なので、このユーザーの処理を停止します。');
                    \Log::debug('    ');
                    break;
                }
            
                /************************************************
                システムとしてのリクエスト制限に関する処理
                *************************************************/
                // フォロー実行時、24時間後のリクエスト制限解除時刻をDBに登録する
                // リクエスト制限に掛からなくても、この時刻を経過すればカウントがリセットされるようにする
                if ($system_follow_release_time === null) {
                    $SystemManager->one_day_system_follow_release_time = Carbon::now()->addHours(24);
                    $SystemManager->update();
                    $system_follow_release_time = $SystemManager->one_day_system_follow_release_time;
                    Log::debug('アプリ単位での24時間後のリクエスト制限解除時刻です。');
                    Log::debug('    ');
                } elseif ($system_follow_release_time !== null) {
                    // 既に解除用の時刻が格納されていれば何もしない
                    Log::debug('アプリ単位でのリクエスト制限解除時刻は既に格納されています。');
                    Log::debug('    ');
                }
                // アプリ単位のリクエスト制限解除時刻より、現在の時刻のほうが進んでいたら制限を解除する
                if ($now_time > $system_follow_release_time) {
                    $SystemManager->one_day_system_follow_release_time = Carbon::now()->addHours(24);
                    $SystemManager->one_day_system_counter = 0;
                    $SystemManager->update();
                    $one_day_system_counter = $SystemManager->one_day_system_counter;
                    $system_follow_release_time = $SystemManager->system_follow_release_time;
                    Log::debug('24時間経過しました。新たに制限解除用の時刻を格納し、カウンターをリセットします。');
                    Log::debug('    ');
                }
                // アプリとしての1日のフォローリクエスト制限以内か判定
                if ($one_day_system_counter >= self::SYSTEM_FOLLOW_LIMIT) {
                    Log::debug('アプリ単位でのリクエスト制限を超えました 990/日。処理を停止します。');
                    Log::debug('    ');
                    exit();
                }

                /************************************************
                ユーザー単位としてのリクエスト制限に関する処理
                *************************************************/
                // フォロー実行時、24時間後のリクエスト制限解除時刻をDBに登録する
                // リクエスト制限に掛からなくても、この時刻を経過すればカウントがリセットされるようにする
                if ($day_follow_release_time === null) {
                    $user->day_follow_release_time = Carbon::now()->addHours(24);
                    $user->update();
                    $day_follow_release_time = $user->day_follow_release_time;
                    Log::debug('ユーザー単位での24時間後のリクエスト制限解除時刻です');
                    Log::debug('    ');
                } elseif ($day_follow_release_time !== null) {
                    // 既に解除用の時刻が格納されていれば何もしない
                    Log::debug('ユーザー単位でのリクエスト制限解除時刻は既に格納されています。');
                    Log::debug('    ');
                }
                // ユーザー個別のリクエスト制限解除時刻より、現在の時刻のほうが進んでいたら制限を解除する
                if ($now_time > $day_follow_release_time) {
                    $user->day_follow_release_time = Carbon::now()->addHours(24);
                    $user->day_follow_limit_count = 0;
                    $user->update();
                    $day_follow_limit_count = $user->day_follow_limit_count;
                    $day_follow_release_time = $user->day_follow_release_time;
                    Log::debug('24時間経過しました。新たに制限解除用の時刻を格納し、カウンターをリセットします。');
                    Log::debug('    ');
                }
                // ユーザー単位の1日のフォローリクエスト制限以内か判定
                // リクエスト制限を超えたらそのユーザーのループ処理を抜け、次のユーザーへ移行する
                if ($day_follow_limit_count >= self::DAY_FOLLOW_LIMIT) {
                    Log::debug('ユーザー単位のリクエスト制限を超えました 400/日。処理を停止します。');
                    Log::debug('    ');
                    break;
                }
                /************************************************
                15/15分リクエスト制限に関する処理
                *************************************************/
                if ($day_follow_quarter_release_time === null) {
                    $user->day_follow_quarter_release_time = new Carbon('+15 minutes');
                    $user->update();
                    $day_follow_quarter_release_time = $user->day_follow_quarter_release_time;
                    Log::debug('15/15分リクエスト制限解除時刻です。');
                    Log::debug('    ');
                } elseif ($day_follow_quarter_release_time !== null) {
                    // 既に解除用の時刻が格納されていれば何もしない
                    Log::debug('15/15分リクエスト制限解除時刻は既に格納されています。');
                    Log::debug('    ');
                }
                // 個別フォロー処理時のリクエスト制限解除時刻より、現在の時刻のほうが進んでいたら制限を解除する
                if ($now_time > $day_follow_quarter_release_time) {
                    $user->day_follow_quarter_release_time = new Carbon('+15 minutes');
                    $user->day_follow_quarter_limit_count = 0;
                    $user->update();
                    $day_follow_quarter_limit_count = $user->day_follow_quarter_limit_count;
                    $day_follow_quarter_release_time = $user->day_follow_quarter_release_time;
                    Log::debug('15分経過しました。新たに制限解除用の時刻を格納し、カウンターをリセットします。');
                    Log::debug('    ');
                }
                // 15/15分制限以内か判定
                if ($day_follow_quarter_limit_count >= self::AUTO_FOLLOW_QUARTER_LIMIT) {
                    Log::debug('15/15分リクエスト制限を超えました。次のユーザーへ移行します。');
                    // Log::debug('15/15分リクエスト制限を超えました。処理を停止します。');
                    Log::debug('    ');
                    break;
                }
               
                /****************************************
                 * フォローを実施する処理
                // ******************************************/
                $result = $connection->post('friendships/create', [
                            'user_id' => $follow_target_id
                            ]);

                // APIへのリクエスト後、リミット数をカウント
                ++$day_follow_quarter_limit_count; // 15/15分フォロー制限用のカウント
                ++$day_follow_limit_count; // 1日395フォロー制限用のカウント
                ++$one_day_system_counter; // 1日1000フォロー制限用のカウント（アプリ全体）
                $user->day_follow_quarter_limit_count = $day_follow_quarter_limit_count;
                $user->day_follow_limit_count = $day_follow_limit_count;
                $user->update();
                $SystemManager->one_day_system_counter = $one_day_system_counter;
                $SystemManager->update();

                Log::debug('15/15分フォロー制限用のカウント  ' .$day_follow_quarter_limit_count);
                Log::debug('    ');
                Log::debug('1日395フォロー制限用のカウント  ' .$day_follow_limit_count);
                Log::debug('    ');
                Log::debug('1日1000フォロー制限用のカウント（アプリ全体）  ' .$one_day_system_counter);
                Log::debug('    ');
                
                // エラーハンドリング
                if ($connection->getLastHttpCode() == 200) {
                    // followsテーブルへ登録
                    $this->addFollowTable($auto_follow_run_user_item->id, $follow_target_id);
                } elseif ($result->errors[0]->code === 108) {
                    // フォローした際のエラーコードが、Twitter上に存在しないユーザーという意味であれば、DBに登録しているユーザーを削除する
                    \Log::debug('Twitter上に存在しないユーザーです。Twitter_ID：'. $follow_target_id);
                    \Log::debug('エラー内容を取得します '. print_r($result, true));
                    \Log::debug('   ');
                    \Log::debug('存在しないユーザーをDB上から削除します');

                    // Twitterから存在していないユーザーの情報をDBから削除する
                    Twuser::where('id', $follow_target_id)->delete();

                    \Log::debug('削除後、一度処理を停止します。');
                    \Log::debug('   ');
                    exit();
                } else {
                    // APIエラーが発生した場合は処理を停止する
                    \Log::debug('リクエスト時にエラーが発生しています。Twitter_ID：'. $follow_target_id);
                    \Log::debug('エラー内容を取得して処理を停止します。 '. print_r($result, true));
                    \Log::debug('   ');
                    exit();
                }


                \Log::debug('フォローする間隔を3秒あける');
                \Log::debug('    ');
                sleep(3);
            }

            \Log::debug(' foreach ($follow_target_list as $follow_target_id) の処理を抜けて、次のユーザーへ移行します。');
            \Log::debug('    ');
        }
     

        \Log::debug('=====================================================================');
        \Log::debug('AutoFollow : 終了');
        \Log::debug('=====================================================================');
    }
    
    // 自分のフォローしているユーザーを取得する
    public function fetchFollowTarget($twitter_id, $connection)
    {
        // 15分毎15リクエストが上限です
        
        $result = $connection->get('friends/ids', [
            'user_id' => $twitter_id
            ])->ids;
        
        //\Log::debug('取得結果 : ' .print_r($result, true));
        return $result;
    }

    // DBからTwitterユーザー情報を取得する
    public function getTwitterUser()
    {
        // DBより仮想通貨関連のアカウントを取得
        $dbresult = Twuser::all();
        if ($dbresult->isNotEmpty()) {
            foreach ($dbresult as $item) {
                $twitterUserList[] = $item->id;
            }
            \Log::debug('DBに登録されているユーザーを返しています。getTwitterUserメソッド');
            \Log::debug('   ');
            // DBに登録されているユーザーを返す
            return $twitterUserList;
        } else {
            \Log::debug('空のコレクションを配列に変換して返却。getTwitterUserメソッド');
            \Log::debug('   ');
            // 空のコレクションを配列に変換して返却
            return $dbresult->toArray();
        }
    }
    
    // 自動フォロー時に使用するインスタンスを生成する
    public function autoFollowAuth($twitter_user_token, $twitter_user_token_secret)
    {
        \Log::debug('=== インスタンスを生成します === ');

        // ヘルパー関数のconfigメソッドを通じて、config/services.phpのtwitterの登録した中身を参照
        $config = config('services.twitter');
        // APIキーを格納
        $api_key = $config['client_id'];
        $api_key_secret = $config['client_secret'];
        // アクセストークンを格納
        $access_token = $twitter_user_token;
        $access_token_secret = $twitter_user_token_secret;
    
        $OAuth = new TwitterOAuth($api_key, $api_key_secret, $access_token, $access_token_secret);
    
        return $OAuth;
    }

    // followsテーブルへの登録
    public function addFollowTable($userID, $twUserID)
    {
        try {
            Follow::create([
                    'user_id' => $userID,
                    'twuser_id' => $twUserID
                ]);
    
            \Log::debug('フォローしたユーザーをfollowsテーブルへ登録しました： ID'. $twUserID);
            \Log::debug('   ');
        } catch (Exception $e) {
            \Log::debug('例外が発生しました。処理を停止します。' . $e->getMessage());
            exit();
        }
    }
}
