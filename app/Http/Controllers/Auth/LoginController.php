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

    \Log::debug('twitter未登録のユーザーです');
    return redirect()->intended($this->redirectPath());
  }

  // オーバーライド
  protected function credentials(Request $request)
  {
      $temporary = $request->only($this->username(), 'password');
      // 論理削除フラグが立っていないユーザーを検索するパラメータを追加
      $temporary['delete_flg'] = 0;

      return $temporary;
  }

  /*********************************************************
   * Twitterログイン・新規登録
   *********************************************************/
  // Twitterアプリ側へリダイレクト
  public function getTwitterLogin()
  {
    // 
    // Twitterアプリ側に認証を求めていく処理
    return Socialite::driver('twitter')->redirect();
  }

  // Twitter認証ページからリダイレクトを受け取り、レスポンスデータを元に新規登録するか否か決定する
  public function getTwitterCallback()
  {

    try {

      // ユーザーデータの取得とアクセストークンの取得
      $user = Socialite::driver('twitter')->user();
      // 既に登録されているユーザーかチェックする
      $validate = User::where('my_twitter_id', $user->getId())->first();

      // // emailの有無で条件を分ける
      // if(empty($validate)){
      //   \Log::debug('mytwitter_idが無いのでなので未登録ユーザーです');
      //   return redirect('/login')->with('message', '提供された資格情報を持つアカウントは見つかりませんでした。新規登録を行って下さい。');
      // }

      // twitter_idをセッションに保存
      session(['twitter_id' => $user->id]);
      session(['access_token' => $user->token]);
      session(['access_token_secret' => $user->tokenSecret]);
      \Log::debug('認証に成功しました');
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
        // Userモデルで$fillableに設定していないカラムにはINSERTされないので注意
        'name' => $user->getNickname(),
        'email' => $user->getEmail(),
        'my_twitter_id' => $user->getId(),
        'twitter_token' => $user->token,
        'twitter_token_secret' => $user->tokenSecret,
      ]);

    session(['follow_limit_time' => $userInfo->follow_limit_time]);
    \Log::debug('セッション情報を取得します' . print_r(session()->all(), true));
    Auth::login($userInfo);

    // プロフィール編集画面へリダイレクト
    return redirect()->to('/mypage');
  }

  // ログイン後のリダイレクト先をオーバーライド
  // デフォルトだとリダイレクト先が/homeになっている
  public function redirectPath()
  {
    return '/mypage';
    //例）return 'costs/index';
  }
}
