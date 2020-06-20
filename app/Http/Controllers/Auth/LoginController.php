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

  /*********************************************************
   * トレイトのauthenticatedメソッドをオーバーライド
   *********************************************************/
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

    \Log::debug('twitter未登録のユーザーです：' . print_r(session()->all(), true));
    return redirect()->intended($this->redirectPath());
  }

  /*********************************************************
   * Twitterログイン・新規登録
   *********************************************************/
  // Twitterアプリ側へリダイレクト
  public function redirectToTwitterProvider()
  {
    // Twitterアプリ側に認証を求めていく処理
    return Socialite::driver('twitter')->redirect();
  }

  // Twitter認証ページからリダイレクトを受け取り、レスポンスデータを元に新規登録するか否か決定する
  public function handleTwitterCallback()
  {
    try {
      // ユーザーデータの取得とアクセストークンの取得
      $user = Socialite::driver('twitter')->user();
      // twitter_idをセッションに保存
      session(['twitterUser_id' => $user->id]);
      session(['access_token' => $user->token]);
      session(['access_token_secret' => $user->tokenSecret]);
      \Log::debug('認証に成功しました');
      \Log::debug('セッション情報を取得します' . print_r(session()->all(), true));
    } catch (\Exception $e) {
      \Log::debug('ログインに失敗しました');
      // エラーならログイン画面へリダイレクト
      return redirect('/login')->with('message', 'ログインに失敗しました。');
    }

    // 既にTwitterユーザーで登録されているか検索、登録されていなければ新規登録する
    $userInfo = User::firstOrCreate(
      // usersテーブルのtwitter_tokenカラムに同じ値を持つレコードがあるかチェック
      // emailで判断すると本アプリ側や、Twitter側でユーザーがメールアドレスを変更した時に新たに作成されてしまう
      ['twitter_token' => $user->token],
      // twitter_tokenカラムに同じ値がなかった場合は、下記の項目をINSERTする
      [
        'name' => $user->getNickname(),
        'email' => $user->getEmail(),
        'avatar' => $user->getAvatar(),
        'twitter_token' => $user->token,
        'twitter_token_secret' => $user->tokenSecret,
      ]
    );

    Auth::login($userInfo);

    // プロフィール編集画面へリダイレクト
    return redirect()->to('/profile');
  }

  // ログイン後のリダイレクト先をオーバーライド
  // デフォルトだとリダイレクト先が/homeになっている
  public function redirectPath()
  {
    return '/profile';
    //例）return 'costs/index';
  }
}
