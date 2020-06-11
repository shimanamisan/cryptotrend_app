<?php

namespace App\Http\Controllers\Auth;

use App\User; // 追加
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth; // 追加
use App\Providers\RouteServiceProvider;
use Laravel\Socialite\Facades\Socialite; // 追加
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

    // Twitterアプリ側へリダイレクト
    public function redirectToTwitterProvider()
    {
        // Twitterアプリ側に認証を求めていく処理
        return Socialite::driver('twitter')->redirect();
    }

    // Twitter認証
    public function handleTwitterCallback()
    {
        try {
            $user = Socialite::driver('twitter')->user();
            \Log::debug('認証に成功しました');
        } catch (\Exception $e) {
            \Log::debug('ログインに失敗しました');
            // エラーならログイン画面へリダイレクト
            return redirect('/login')->with(
                'auth_error',
                'ログインに失敗しました。'
            );
        }

        $userInfo = User::firstOrCreate(
            // usersテーブルのtwitter_tokenカラムに同じ値を持つレコードがあるかチェック
            // emailで判断するとTwitter側でユーザーがメールアドレスを変更した時に対応できない
            // ['twitter_token' => $user->token],
            // tokenからidに変更
            ['twitter_id' => $user->id],
            // twitter_tokenカラムに同じ値がなかった場合は、下記の項目をINSERTする
            [
                'name' => $user->nickname,
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar(),
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
