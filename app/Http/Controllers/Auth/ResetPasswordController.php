<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str; // 追加
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    // リダイレクト先をログイン画面に変更する
    // protected $redirectTo = RouteServiceProvider::HOME;

    // デフォルトだとパスワードリセット後にログインされるので、ログインさせないようにする
    public function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        // Strクラスの名前空間をインポートしておく
        $user->setRememberToken(Str::random(60));

        $user->save();

        // ResetsPasswordsトレイにはこの後にguardメソッドでログインするようになっていたが
        // ログインさせずにログイン画面へ遷移させるようにする
        // $this->guard()->login($user) // ここでログインするようになっている
    }

    // パスワードリセット後にログイン画面へリダイレクトさせるためにオーバーライドする
    public function redirectPath()
    {
        return '/login';
        //例）return 'costs/index';
    }
}
