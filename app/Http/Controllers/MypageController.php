<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // 追加
use Illuminate\Support\Facades\Auth; // 追加

class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // dd($user);

        Log::debug('プロフィール画面でユーザー情報を取得しています：' . $user);

        return view('mypage', ['user' => $user]);
    }
}
