<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // 追加
use Illuminate\Support\Facades\Auth; // 追加

class MypageController extends Controller
{
    public function index()
    {

        return view('mypage');
    }

    public function getUserData()
    {
        // Ajax処理でユーザー情報を取得
        $user = Auth::user();

        return $user;
    }

    public function delete()
    {
        $user = Auth::user();
    }
}
