<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request; // 追加
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password; // 追加
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
        $this->validateEmail($request);
        // emailを検索してきたときに空だった場合に、未登録ユーザーはパスワード変更メールを飛ばせないようにする

        $response = $this->broker()->sendResetLink($request->only('email'));

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }
}
