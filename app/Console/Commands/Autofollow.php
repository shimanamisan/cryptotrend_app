<?php

namespace App\Console\Commands;

use App\User;
use App\TwitterUser; // ★追記
use App\SystemManager; // ★追記
use Illuminate\Console\Command;
use Abraham\TwitterOAuth\TwitterOAuth; // ★追記

class Autofollow extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'autofollow';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

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
    const AUTO_FOLLOW_LIMIT = 15;
    // 15分を秒に変換、個別でフォローする際の上限に使用する
    const QUARTER_MINUTES = 15 * 60;

  /**
   * Execute the console command.
   *
   * @return mixed
   */
   // DBからTwitterの情報を取得してきて自分のフォロワーと見比べてフォローしていなかったら新規でフォローする
    // リクエスト/24時間、ユーザーあたり400：アプリごとに1000となっている
    // フォローの上限数は15分毎日14とする
    // 1日に1000以上フォローしなようにする

    // 自動フォロー機能呼び出し
    public function handl()
    {
    
      \Log::info('=====================================================================');
      \Log::info('AutoFollow : 開始');
      \Log::info('=====================================================================');
  
        /**
         * 後にシステム全体での上限、990フォローを超えていたら実施しない処理を追記する
         */
        $SystemManager = SystemManager::where('id', 1)->first();

        $one_day_system_counter_limit = $SystemManager->one_day_system_counter;

        if($one_day_system_counter_limit < self::SYSTEM_FOLLOW_LIMIT){

          \Log::debug('1日のアプリ全体としてのフォロー上限を以内なのでフォローを継続します '. $one_day_system_counter_limit.'/990回');
          \Log::debug('    ');

            // DBからautofollowカラムが1のユーザーを取得
            $auto_follow_run_user_list = User::where('autofollow_status', self::AUTO_FOLLOW_STATUS_RUN)->get();
          \Log::debug('=== 自動フォローステータスがONのユーザーを取得しています:handlメソッド ===');
          \Log::debug(count($auto_follow_run_user_list). ' 人のユーザーが自動フォローをONにしています。');
    
            if($auto_follow_run_user_list->isEmpty()){
              \Log::debug('自動フォローのユーザーが存在していないため処理を終了します');
              \Log::debug('    ');
              exit();
            }
            foreach($auto_follow_run_user_list as $auto_follow_run_user_item) {
                $user = $auto_follow_run_user_item; // 各ユーザーのUserモデル
                $twitter_id = $auto_follow_run_user_item->my_twitter_id;
                $twitter_user_token = $auto_follow_run_user_item->twitter_token;
                $twitter_user_token_secret = $auto_follow_run_user_item->twitter_token_secret;
                $follow_limit_count = $auto_follow_run_user_item->follow_limit_count;
                
                if($follow_limit_count < self::DAY_FOLLOW_LIMIT){
                  \Log::debug($user->name. ' さんはまだ1日の上限ではないので処理を継続します '.$follow_limit_count.'/395');
                    // 1ユーザー当たり1日の上限、395フォロー以下だったら自動フォローを実行する
                    // ステータスがONのユーザーの数だけ下記のオートフォローが実行される
                    $this->autoFollow($user, $twitter_id, $twitter_user_token, $twitter_user_token_secret, $follow_limit_count, $one_day_system_counter_limit, $SystemManager);
                    
                }else{
                  \Log::debug($user->name.' さんは1日の上限を超えていたので処理を終了します '.$follow_limit_count.'/395');
                  \Log::debug('    ');
                    continue;
                }
            }

        }else{
          \Log::debug('1日のアプリ全体としてのフォロー上限を上回っているので処理を停止します '. $one_day_system_counter_limit.'/990回');
          \Log::debug('    ');
            exit();
        }

      \Log::debug('=====================================================================');
      \Log::debug('AutoFollow : 終了');
      \Log::debug('=====================================================================');
    }

    // 自動フォロー機能
    public function autoFollow($user, $twitter_id, $twitter_user_token, $twitter_user_token_secret, $follow_limit_count, $one_day_system_counter_limit, $SystemManager)
    {   
        // 1回の処理で15フォローを超えないようにする
        $system_follow_counter_quarter_minutes = 0;

        // インスタンスを生成
        $connect = $this->autoFollowAuth($twitter_user_token, $twitter_user_token_secret);

        // DBに登録されているユーザを取得
        $twitterUserList = $this->getTwitterUser();

        // フォローしているユーザーを取得
        $follower_list = $this->fetchFollowTarget($twitter_id, $twitterUserList, $connect);
        // フォローしているユーザーのIDとDBに登録されているIDの差分を取得する。一致していないもの（フォローしていないユーザー）を取得する
        // 第一引数が比較元の配列、第二引数に比較する配列を指定する
        // 配列を比較して重複していない値のみ出力（第二引数の配列の値と一致したものは除外される）
        // 比較元の配列を比較対象の配列と比較し、比較元の配列にしかない値のみを取得
        $follow_target_list = array_diff($twitterUserList, $follower_list);
     
        // dd($follow_target_list);
        // 全てフォローしてリストが空だったら処理を停止
        foreach($follow_target_list as $follow_target_id){

            if(empty($follow_target_list)){
              \Log::debug('リストが空なら処理を停止します。'. print_r($follow_target_list, true));
              \Log::debug('    ');
                break;
            }

            if($follow_limit_count < self::DAY_FOLLOW_LIMIT && $one_day_system_counter_limit < self::SYSTEM_FOLLOW_LIMIT){
              \Log::debug('1日のユーザーのフォロー制限回数です '. $follow_limit_count.'/395');
              \Log::debug('1日のアプリ全体としてのフォロー制限回数です '. $one_day_system_counter_limit.'/990回');
              \Log::debug('    ');
                /**
                 * アプリ全体として990フォローを超えないように自動フォロー中もカウントする必要がある
                 */
                ++$one_day_system_counter_limit;
                $SystemManager->one_day_system_counter = $one_day_system_counter_limit;
                $SystemManager->update();

                // 1日にフォローできる数をカウントしていく（usersテーブルより取得）
                ++$follow_limit_count;
                $user->follow_limit_count = $follow_limit_count;
                $user->update();

                // 15分毎に15フォローを超えないようにするカウンター
                ++$system_follow_counter_quarter_minutes;

                if($system_follow_counter_quarter_minutes < 15 ){
                  \Log::debug('まだ '. $system_follow_counter_quarter_minutes . ' 回目のループなのでフォローを継続します');
                    
                    // フォローを実施する
                    $result = $connect->post('friendships/create', [
                        'user_id' => $follow_target_id
                    ]);
                }else{
                  \Log::debug($system_follow_counter_quarter_minutes . ' 回目、フォローの上限を超えました。処理を停止します。');
                    
                    $system_follow_counter_quarter_minutes = 0;
                  \Log::debug('単位ユーザ当たりのカウンターをリセットします。$system_follow_counter_quarter_minutes：'. $system_follow_counter_quarter_minutes);
                    $release_limit_time = time() + self::QUARTER_MINUTES;
                  \Log::debug('単位ユーザー当たりのAPI制限解除時間をDBへ保存します UNIX_TIME_STAMP: '.$release_limit_time);
                  \Log::debug('    ');
                    $user->follow_limit_time = $release_limit_time;
                    $user->update();
                  \Log::debug('API制限に掛かる手前で停止しています。900秒待機します');
                  \Log::debug('    ');
                  sleep(900);
                    // break;
                }
              \Log::debug('連続でフォローしすぎてアカウント凍結されないように3秒間隔をあける');
              \Log::debug('    ');
                sleep(3);

            }elseif($follow_limit_count == self::DAY_FOLLOW_LIMIT){
              \Log::debug($user->name.' さんは1日のユーザーのフォロー制限回数を超えました '. $follow_limit_count.'/395');
              \Log::debug('    ');
             
                break;
            }else{
              \Log::debug('1日のアプリ全体としてのフォロー回数です '. $one_day_system_counter_limit.'/990');
              \Log::debug('    ');
            break;
            }

        }

    }
    
    // 自分のフォローしているユーザーを取得する
    public function fetchFollowTarget($twitter_id, $twitterUserList, $connect)
    {   
        // 15分毎15リクエストが上限です
        $result = $connect->get('friends/ids', [
            'user_id' => $twitter_id
            ])->ids;
            //\Log::debug('取得結果 : ' .print_r($result, true));
        return $result;
    }

    // DBからTwitterユーザー情報を取得する
    public function getTwitterUser()
    {
        // DBより仮想通貨関連のアカウントを取得
        $dbresult = TwitterUser::all();

        foreach ($dbresult as $item) {
            $twitterUserList[] = $item->twitter_id;
        }
        
        return $twitterUserList;
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



}
