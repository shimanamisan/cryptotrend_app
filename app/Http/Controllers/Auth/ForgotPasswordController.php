<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Validation\Rule; // 追加
use Illuminate\Http\Request; // 追加
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password; // 追加
use Illuminate\Support\Facades\Validator; // 追加
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    // /*************************************************************
    // SendsPasswordResetEmails トレイトのメソッドをオーバーライド
    // **************************************************************/
    public function sendResetLinkEmail(Request $request)
    {
        // emailを検索してきたときに空だった場合に、未登録及び退会済ユーザーは
        // バリデーションに引っかかるようにする
        $this->validator($request->all())->validate();
        
        $response = $this->broker()->sendResetLink($request->only('email'));

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    protected function validator(array $data)
    {
        // カスタムエラーメッセージ
        $message = [
            'email' => '有効なメールアドレスを指定してください。',
            'email.unique' => 'メールアドレスに一致するユーザーは存在していません。',
        ];

        return Validator::make($data, [
        
            'email' => ['required', 'string', 'email', 'max:100',
                        // usersテーブルで退会済みでないユーザーが存在しているか探す（deletef_flgが0のユーザー）
                        Rule::exists('users', 'email')->where('delete_flg', 0)]
        ], $message);
    }
}
