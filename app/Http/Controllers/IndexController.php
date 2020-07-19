<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ContactRequest;

class IndexController extends Controller
{
    
    // トップページの表示
    public function home()
    {
        return view('home');
    }

    // // お問い合わせページの表示
    // public function contact()
    // {
    //     return view('contact.index');
    // }

    // // お問い合わせ内容の確認
    // public function confirm(ContactRequest $request)
    // {
    //     // フォームリクエストクラスでバリデーション後、
    //     // フォームから受け取った全ての値を入力確認ページへ渡す
    //     $form_value = $request->all();

    //     return view('contact.confirm', ['form_value' => $form_value]);
    // }

    // // お問い合わせ内容の送信
    // public function finish(ContactRequest $request)
    // {

    // }
}
