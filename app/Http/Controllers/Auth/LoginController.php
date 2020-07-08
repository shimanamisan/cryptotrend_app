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
}
