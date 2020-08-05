<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request; // 追加
use Illuminate\Support\Str; // 追加
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\PasswordReset; // 追加
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

    // /*************************************************************
    // ResetsPasswords トレイトのメソッドをオーバーライド
    // **************************************************************/
    public function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        \Log::debug('パスワードを変更しました。ログイン画面へ遷移します');
        \Log::debug('   ');

        // ResetsPasswordsトレイにはこの後にguardメソッドでログインするようになっていたが
        // ログインさせずにログイン画面へ遷移させるようにする

        return $user;
    }

    // パスワード変更時にフラッシュメッセージを出力するようにセッションの名前を変更
    protected function sendResetResponse(Request $request, $response)
    {
        return redirect($this->redirectPath())
                            ->with('system_message', trans($response));
    }

    protected function rules()
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed|min:8|max:100|regex:/^[a-zA-Z0-9]+$/'
        ];
    }

    protected function validationErrorMessages()
    {
        return [
            'email.email' => '有効なメールアドレスを指定してください。',
            'password.regex' => '半角英数のみご利用いただけます。',
            'password.confirmed' => ':attributeと、:attribute再入力が一致していません。',
        ];
    }

    // パスワードリセット後にログイン画面へリダイレクトさせるためにオーバーライドする
    public function redirectPath()
    {
        return '/login';
    }
}
