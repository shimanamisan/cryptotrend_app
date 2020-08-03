<?php

namespace App\Http\Controllers\Auth;

use App\Follow; // ★追加
use App\User; // ★追加
use App\Twuser; // ★追加
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth; // ★追加
use Abraham\TwitterOAuth\TwitterOAuth; // ★追加
use Laravel\Socialite\Facades\Socialite; // ★追加
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class TwitterAuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*********************************************************
    * Twitterアカウント新規登録
    *********************************************************/
    public function getTwitterRegister()
    {
        return Socialite::driver('twitter')->redirect();
    }

    // Twitter認証ページからリダイレクトを受け取り、レスポンスデータを元に新規登録するか否か決定する
    public function getTwitterCallback()
    {
        try {
            // ユーザーデータの取得とアクセストークンの取得
            $twuser = Socialite::driver('twitter')->user();
        
            \Log::debug('ログイン中のアカウントに、Twitterアカウントを新規登録します');
            \Log::debug('   ');

            // ログイン中であるか確認する
            if (Auth::check()) {

                // ログイン中のユーザーを取得する
                $authUser = Auth::user();
                // 新規登録時の登録処理を関数に切り出し
                $userInfo = $this->findCreateUser($twuser, $authUser);

                // ユーザーが正しく取得でき、Twitter認証情報を保存出来ていたら下記の処理を行う
                if ($userInfo !== null) {

                    // セッションにTwitterユーザー情報を入れる
                    session(['twitter_id' => $userInfo->my_twitter_id]);
                    session(['access_token' => $userInfo->twitter_token]);
                    session(['access_token_secret' => $userInfo->twitter_token_secret]);
                    \Log::debug('新規登録に成功しました。セッションを格納し、画面遷移します');
                    \Log::debug('   ');
        
                    /***************************************************************************
                     * DB側の関連ユーザーとTwitterアカウントのフォローしているユーザーを比較し、
                     * 一致していないユーザーがいればfollowsテーブルに登録する
                    ****************************************************************************/
                    // DBに登録している仮想通貨関連のアカウントを取得
                    $twitterUserList = $this->getTwitterUser();
                    
                    // DBに登録されている仮想通貨関連のアカウントが空だったら以降の処理は行わない
                    if (empty($twitterUserList)) {
                        \Log::debug('DBに登録している仮想通貨関連のアカウントがありませんでした');
                        \Log::debug('   ');
                    } else {
                        // TwitterOAuthをインスタンス化
                        $connection = $this->newConnection($twuser->token, $twuser->tokenSecret);
            
                        // 自分のフォローしているユーザーを取得する
                        $follow_list = $this->fetchFollowTarget($twuser->id, $connection);
        
                        // フォローしているユーザーがいない場合は以降の処理は行わない
                        if (!empty($follow_list)) {
                            \Log::debug('自分のフォローしているユーザーを取得しています');
                            \Log::debug('   ');
               
                            // 配列を比較し共通しているものを出力する
                            $follow_list_intersect = array_intersect($twitterUserList, $follow_list);
        
                            // 重複しているユーザーがいれば、followsテーブルに登録する
                            if (!empty($follow_list_intersect)) {
                                foreach ($follow_list_intersect as $follow_user_id) {
                                    \Log::debug('DBと重複してるフォロー済みのユーザーをfollowsテーブルに登録しています  TwitterID：'. $follow_user_id. '  UserID：'. $userInfo->id);
                                    \Log::debug('   ');

                                    Follow::updateOrCreate(
                                        ['user_id' => $userInfo->id, 'twuser_id' => $follow_user_id, 'delete_flg' => 0],
                                        [
                                            'user_id' => $userInfo->id,
                                            'twuser_id' => $follow_user_id
                                            ]
                                    );
                                }
                            }
                        } else {
                            \Log::debug('DBと重複してるフォロー済みのユーザーはいませんでした。');
                            \Log::debug('   ');
                        }
                    }
                }
 
                // トレンド一覧画面へリダイレクト
                return redirect()->to('/twuserlist');
            } else {
                
                // 関連アカウント一覧ページ自体ログインしていなければ見れないページだが、もしログインしてない状態のユーザー及び
                // 有効期限が切れているユーザーであれば、メッセージを格納しログイン画面へ遷移させる
                return redirect('/login')->with('error_message', 'ログイン有効期限が切れました。再度ログインしてからTwitterアカウント認証を行ってください。');
            }
        } catch (\Exception $e) {
            \Log::debug('ログインに失敗しました。例外の処理に入っています。' . $e->getMessage());
            \Log::debug('スタックトレース：' . $e->getTraceAsString());
            \Log::debug('   ');

            // Twitterアカウント認証中に例外処理が発生したらログイン画面に戻る
            session()->invalidate();
            session()->regenerateToken();
                
            // 例外処理が発生したらログイン画面に戻る
            return redirect('/login')->with('error_message', 'Twitterアカウント認証に失敗しました。しばらく経過してから、再度ログインして実行してください。');
        }
    }

    // Twitter認証で新規ユーザー登録の処理
    public function findCreateUser($twuser, $authUser)
    {
        // 既に同じTwitterアカウントが登録されていないか検索する
        $checkTwuser = User::where('my_twitter_id', $twuser->getId())
        ->where('delete_flg', 0)
        ->first();

        if (!empty($checkTwuser)) {
            \Log::debug('既に登録済みのTwitterアカウントです。');
            \Log::debug('   ');
            return redirect('/twuserlist')->with('error_message', '既に認証されているアカウントです。他のTwitterアカウントを選択してください。');
        }

        // メールアドレスを元に退会していないユーザーを検索
        $userInfo = $authUser->where('email', $authUser->email)
        ->where('delete_flg', 0)
        ->first();
        
        // ユーザーが正しく取得できたらTwitterIDなど登録する
        if (!empty($userInfo)) {
            $userInfo->fill([
                'my_twitter_id' => $twuser->getId(),
                'twitter_token' => $twuser->token,
                'twitter_token_secret' => $twuser->tokenSecret,
            ]);

            \Log::debug('新規でTwitter認証を行いました。');
            \Log::debug('   ');

            $userInfo->save();
            // モデルオブジェクトを返却
            return $userInfo;
        }

        // ユーザーが見つからなかった場合は、null値が返却される
        return $userInfo;
    }

    // 自分のフォローしているユーザーを取得する
    public function fetchFollowTarget($twitter_id, $connection)
    {
        \Log::debug('fetchFollowTargetメソッドが実行されています');
        \Log::debug('   ');
        // 15分毎15リクエストが上限です
        $result = $connection->get('friends/ids', [
          'user_id' => $twitter_id
          ])->ids;

        // 通信成功時、リクエスト制限が掛かっていない場合
        if ($connection->getLastHttpCode() == 200) {
            \Log::debug('ステータスコードが200で情報が取得できています。');
            \Log::debug('   ');
            return $result;
        } else {
            \Log::debug('API制限に掛かっています。空の配列を返します。');
            \Log::debug('   ');
            $result = [];
            return $result;
        }
    }

    // TwitterOAuthインスタンスを生成する
    public function newConnection($twitter_user_token, $twitter_user_token_secret)
    {
        \Log::debug('=== インスタンスを生成します === ');
        \Log::debug('   ');
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

    // DBからTwitterユーザー情報を取得する
    public function getTwitterUser()
    {
        // DBより仮想通貨関連のアカウントを取得
        $dbresult = Twuser::all();
        // dd($dbresult);
        if ($dbresult->isNotEmpty()) {
            foreach ($dbresult as $item) {
                $twitterUserList[] = $item->id;
            }
            \Log::debug('DBに登録されているユーザーを返しています。');
            \Log::debug('   ');
            // DBに登録されているユーザーを返す
            return $twitterUserList;
        } else {
            \Log::debug('空のコレクションを配列に変換して返却。');
            \Log::debug('   ');
            // 空のコレクションを配列に変換して返却
            return $dbresult->toArray();
        }
    }
}
