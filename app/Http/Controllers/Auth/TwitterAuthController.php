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
        $this->middleware('guest')->except('logout');
    }

    /*********************************************************
    * Twitterログイン・新規登録
    *********************************************************/
    // Twitter認証済みで、ログインするときの処理
    public function getTwitterLogin()
    {
        // ログインの処理であることを判断するためにセッションにフラグを入れる
        session(['login_flg' => true]);
        return Socialite::driver('twitter')->redirect();
    }

    // 初回Twitter認証時の処理
    public function getTwitterRegister()
    {
        // 初回登録時の処理
        return Socialite::driver('twitter')->redirect();
    }

    // Twitter認証ページからリダイレクトを受け取り、レスポンスデータを元に新規登録するか否か決定する
    public function getTwitterCallback()
    {
        try {
            // ユーザーデータの取得とアクセストークンの取得
            $user = Socialite::driver('twitter')->user();
        
            /****************************************************
             *ログイン画面からリダイレクトしてきたときの処理
            *****************************************************/
            if (session()->has('login_flg')) {
                \Log::debug('ログイン時の処理です');
                \Log::debug('   ');
                
                // delete_flgが立っていないユーザーのメールアドレスが未登録の場合は、ユーザー登録していないユーザーと判定する
                $userInfo = User::where('email', $user->getEmail())
                                  ->where('delete_flg', 0)
                                  ->first();
                
                // twitter_idも同様に格納
                $twUserId = User::where('my_twitter_id', $user->getId())
                                  ->where('delete_flg', 0)
                                  ->first();
                
                // Twitter_id及びメールアドレスが登録されていなかったら未登録ユーザーとする
                if (empty($twUserId) && empty($userInfo)) {
                    \Log::debug('emailかtwitter_idが無いのでなので未登録ユーザーです');
                    \Log::debug('   ');
                    // 画面遷移する前にログインフラグを削除
                    session()->forget('login_flg');
                    // ログイン画面へリダイレクト
                    return redirect('/register')->with('error_message', '提供された資格情報を持つアカウントは見つかりませんでした。新規登録を行って下さい。');
                }

                // emailでユーザー情報が登録されていた場合は、TwitterIDなどをDBへ格納する
                $userInfo->fill([
                    'my_twitter_id' => $user->getId(),
                    'twitter_token' => $user->token,
                    'twitter_token_secret' => $user->tokenSecret,
                ]);

                // dd($userInfo);

                $userInfo->save();

                \Log::debug('認証に成功しました');
                \Log::debug('   ');
            
                Auth::login($userInfo);
                // 画面遷移する前にログインフラグを削除
                session()->forget('login_flg');
                // セッションにTwitterユーザー情報を入れる
                session(['twitter_id' => $user->id]);
                session(['access_token' => $user->token]);
                session(['access_token_secret' => $user->tokenSecret]);
                session(['follow_limit_time' => $userInfo->follow_limit_time]);

                /***************************************************************************
                 * DB側の関連ユーザーとTwitterアカウントのフォローしているユーザーを比較し、
                 * 一致していないユーザーがいればfollowsテーブルに登録する
                ****************************************************************************/
                // DBに登録している仮想通貨関連のアカウントを取得
                $twitterUserList = $this->getTwitterUser();

                if (empty($twitterUserList)) {
                    \Log::debug('DBに登録している仮想通貨関連のアカウントがありませんでした。');
                    \Log::debug('   ');
                } else {
                   
                    // TwitterOAuthをインスタンス化
                    $connection = $this->newConnection($user->token, $user->tokenSecret);
    
                    // 自分のフォローしているユーザーを取得する
                    $follow_list = $this->fetchFollowTarget($user->id, $connection);
    
                    // フォローしているユーザーがいない場合や
                    // DBに登録されているユーザーがいなければ処理を行わない
                    if (!empty($follow_list)) {
                        \Log::debug('自分のフォローしているユーザーを取得しています');
                        \Log::debug('   ');
    
                        // 配列を比較し共通しているものを出力する
                        $follow_list_intersect = array_intersect($twitterUserList, $follow_list);
        
                        // 重複しているユーザーがいれば、followsテーブルに登録する
                        if (!empty($follow_list_intersect)) {
                            // dd($userInfo->id);
                            foreach ($follow_list_intersect as $follow_user_id) {
                                \Log::debug('DBと重複してるフォロー済みのユーザーをfollowsテーブルに登録しています  ユーザーID：'. $userInfo->id);
                                \Log::debug('   ');
                                Follow::updateOrCreate(
                                    ['user_id' => $userInfo->id, 'twuser_id' => $follow_user_id, 'delete_flg' => 0],
                                    [
                                'user_id' => $userInfo->id,
                                'twuser_id' => $follow_user_id
                              ]
                                );
                            }
                        } else {
                            \Log::debug('DBの仮想通貨関連のアカウントと一致するユーザーはいませんでした。');
                            \Log::debug('   ');
                        }
                    }
                }

                // トレンド一覧画面へリダイレクト
                \Log::debug(session()->all());
                return redirect()->to('/coins');
            } else {

            /**********************************************************************
             * login_flgが空だったら新規登録画面からリダイレクトしてきたものと判定
            ***********************************************************************/
                \Log::debug('新規登録画面の処理です');
                \Log::debug('   ');

                // 新規登録時の登録処理を関数に切り出し
                $userInfo = $this->findCreateUser($user);
                
                // セッションにTwitterユーザー情報を入れる
                session(['twitter_id' => $user->id]);
                session(['access_token' => $user->token]);
                session(['access_token_secret' => $user->tokenSecret]);
                session(['follow_limit_time' => $userInfo->follow_limit_time]);
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
                    $connection = $this->newConnection($user->token, $user->tokenSecret);
    
                    // 自分のフォローしているユーザーを取得する
                    $follow_list = $this->fetchFollowTarget($user->id, $connection);

                    // フォローしているユーザーがいない場合は以降の処理は行わない
                    if (!empty($follow_list)) {
                        \Log::debug('自分のフォローしているユーザーを取得しています');
       
                        // 配列を比較し共通しているものを出力する
                        $follow_list_intersect = array_intersect($twitterUserList, $follow_list);

                        // 重複しているユーザーがいれば、followsテーブルに登録する
                        if (!empty($follow_list_intersect)) {
                            foreach ($follow_list_intersect as $follow_user_id) {
                                \Log::debug('DBと重複してるフォロー済みのユーザーをfollowsテーブルに登録しています  ユーザーID：'. $userInfo->id);
                                Follow::updateOrCreate(
                                    ['user_id' => $userInfo->id, 'twuser_id' => $follow_user_id, 'delete_flg' => 0],
                                    [
                                    'user_id' => $userInfo->id,
                                    'twuser_id' => $follow_user_id
                                    ]
                                );
                            }
                        }
                    }
                }
                
                Auth::login($userInfo);
                
                // 画面遷移する前にログインフラグを削除
                session()->forget('login_flg');
                // トレンド一覧画面へリダイレクト
                return redirect()->to('/coins');
            }
        } catch (\Exception $e) {
            \Log::debug('ログインに失敗しました。例外の処理に入っています。' . $e->getMessage());
            \Log::debug('スタックトレース：' . $e->getTraceAsString());
            \Log::debug('   ');

            // ログイン画面から例外処理が発生したらログイン画面に戻る
            if (session()->has('login_flg')) {
                
                // 画面遷移する前にログインフラグを削除
                session()->forget('login_flg');
                session()->invalidate();
                session()->regenerateToken();
                
                return redirect('/login')->with('error_message', 'ログインに失敗しました。');
            } else {
                session()->forget('login_flg');
                session()->invalidate();
                session()->regenerateToken();
                
                // 新規登録画面から例外処理が発生したらログイン画面に戻る
                return redirect('/register')->with('error_message', '新規登録に失敗しました。しばらくお待ち下さい。');
            }
        }
    }

    // Twitter認証で新規ユーザー登録の処理
    public function findCreateUser($user)
    {
        // 退会していないユーザーを検索
        $userInfo = User::where('email', $user->getEmail())
        ->where('delete_flg', 0)
        ->first();
        // メールアドレスで登録済みのユーザーで、新規でTwitter認証をしてきている場合
        if (!empty($userInfo)) {
            $userInfo->fill([
                'my_twitter_id' => $user->getId(),
                'twitter_token' => $user->token,
                'twitter_token_secret' => $user->tokenSecret,
            ]);

            \Log::debug('メールアドレスで登録済みのユーザー若しくは既にTwitterアカウントで登録済みのユーザーが新規登録ボタンをクリックしたときの処理');
            \Log::debug('   ');

            $userInfo->save();
            return $userInfo;
        }

        // 未登録ユーザーだったときの処理
        $newUser = User::create([
            'name' => $user->getNickname(),
            'email' => $user->getEmail(),
            'my_twitter_id' => $user->getId(),
            'twitter_token' => $user->token,
            'twitter_token_secret' => $user->tokenSecret,
        ]);

        \Log::debug('未登録ユーザーだったときの処理');
        \Log::debug('   ');

        return $newUser;
    }

    // 自分のフォローしているユーザーを取得する
    public function fetchFollowTarget($twitter_id, $connection)
    {
        \Log::debug('fetchFollowTargetメソッドが実行されています');
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
