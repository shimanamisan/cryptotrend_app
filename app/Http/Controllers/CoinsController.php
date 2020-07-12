<?php

namespace App\Http\Controllers;

use App\Coin; // ★追記
use Carbon\Carbon; // ★追記
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Abraham\TwitterOAuth\TwitterOAuth; // ★追記
use Abraham\TwitterOAuth\TwitterOAuthException; // ★追記
use Laravel\Socialite\Facades\Socialite; // 追加


class CoinsController extends Controller
{
    // search/tweetsのリクエストの上限は、15分毎450回（アプリケーション認証時）
    const SEARCH_REQUEST_LIMIT = 450;
    // 
    const REQUEST_LIMIT＿MINUTES = 15;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // ビューファイルを表示させる
        return view('coins');
    }

    public function getHourCoins()
    {
        $coins = DB::table('coins')
        ->join('hours', 'coins.id', '=', 'hours.coin_id')
        ->get();

        return $coins;
    }

    public function getDayCoins()
    {
        $coins = DB::table('coins')
        ->join('days', 'coins.id', '=', 'days.coin_id')
        ->get();

        return $coins;
    }
    
    public function getWeekCoins()
    {
        $coins = DB::table('coins')
        ->join('weeks', 'coins.id', '=', 'weeks.coin_id')
        ->get();

        return $coins;
    }

}

