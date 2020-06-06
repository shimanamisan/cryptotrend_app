<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite; // 追加

class CoinsController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function getCondInfo()
    {
    }
}
