<?php

namespace App\Http\Controllers\Auth;

use App\User; // ★追加
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth; // ★追加
use Laravel\Socialite\Facades\Socialite; // ★追加
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
  /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

  use AuthenticatesUsers;

  /**
   * Where to redirect users after login.
   *
   * @var string
   */
  // protected $redirectTo = RouteServiceProvider::HOME;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('guest')->except('logout');
  }

  /******************************************************************
   * AuthenticatesUsersトレイトのauthenticatedメソッドをオーバーライド
   ******************************************************************/
  // 引数の$userに$this->guard()->user()の認証済みユーザーの情報が入ってくる
  // この関数はトレイト側では空になっているので、常にredirectPathメソッドでリダイレクトされる
  // このメソッドに認証後の処理を挟んでログイン後の画面へ遷移させるようにしている
  protected function authenticated(Request $request, $user)
  {

    // twitter認証済みのユーザーであればtokenを格納する
    $access_token = $user->twitter_token;
    $access_token_secret = $user->twitter_token_secret;
    if (!empty($access_token) && !empty($access_token_secret)) {
      session(['access_token' => $access_token]);
      session(['access_token_secret' => $access_token_secret]);
      \Log::debug('twitter_token及びtwitter_token_secretが空でない場合はセッションに格納します：' . print_r(session()->all(), true));

      /*******
       * DB側の関連ユーザーとTwitterアカウントのフォローしているユーザーを比較し、
       * 一致していないユーザーがいればfollowsテーブルに登録する
      ********/





      return redirect()->intended($this->redirectPath());
    }

    \Log::debug('twitter未登録のユーザーです');
    return redirect()->intended($this->redirectPath());
  }
  
  /******************************************************************
   * AuthenticatesUsersトレイトのcredentialsメソッドをオーバーライド
  ******************************************************************/
  // メールアドレスログイン時、delete_flgが立っていないユーザーを検索するよう条件を追加
  protected function credentials(Request $request)
  {
      $temporary = $request->only($this->username(), 'password');
      // 論理削除フラグが立っていないユーザーを検索するパラメータを追加
      $temporary['delete_flg'] = 0;

      return $temporary;
  }

  /******************************************************************
   * AuthenticatesUsersトレイトのcredentialsメソッドをオーバーライド
  ******************************************************************/
  protected function validateLogin(Request $request)
  {
    $request->validate([
      $this->username() => 'required|string|email|max:255',
      'password' => 'required|string',
    ],
    [
      'email.email' => '有効なメールアドレスを指定してください。'
    ]);
  }

  /******************************************************************
   * RedirectsUsersトレイトのredirectPathメソッドをオーバーライド
  ******************************************************************/
  // AuthenticatesUsersトレイトで読み込まれているRedirectsUsersトレイトのredirectPathメソッドを上書き
  // ログイン後のリダイレクト先を変更
  // デフォルトだとリダイレクト先が/homeになっている
  public function redirectPath()
  {
    return '/mypage';
    //例）return 'costs/index';
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
      $dbresult = Twuser::all();

      foreach ($dbresult as $item) {
          $twitterUserList[] = $item->id;
      }
      
      return $twitterUserList;
  }
  
  // TwitterOAuthインスタンスを生成する
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
