<?php

use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// トップページのページの表示
Route::get('/', 'IndexController@home')->name('home');
// ログアウト
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
// Twitter経由でのユーザー登録を行う
Route::get('register/twitter', 'Auth\TwitterAuthController@getTwitterRegister')->name('twitter.register');
// アプリ側から情報が返ってくるURL
Route::get('auth/twitter/callback', 'Auth\TwitterAuthController@getTwitterCallback');

Auth::routes();

// 認証（ログイン）していなければ表示させないページ
Route::group(['middleware' => 'auth'], function () {
    
    // 仮想通貨関連のニュースの取得
    Route::get('/news', 'NewsController@index')->name('getnews.index');
    /**********************************************
     * ユーザーフォロー機能関連のルーティング
     **********************************************/
    // 仮想通貨関連のTwitterユーザーページを表示
    Route::get('/twuserlist', 'TwitterController@index')->name('userlist.index');
    // Ajax処理：ユーザーをフォローする
    Route::post('/follow', 'FollowController@follow');
    // // Ajax処理：フォローを外す
    Route::post('/unfollow', 'FollowController@unfollow');
    // Ajax処理：自動フォロー機能をONにする
    Route::post('/autofollow', 'FollowController@autoFollowFlg');
    
    /*****************************************
     * トレンド表示機能関連のルーティング
     *****************************************/
    // 仮想通貨情報のページを表示
    Route::get('/coins', 'CoinsController@index')->name('conins.index');
    // Ajax処理：過去1時間のツイート数を取得するエンドポイント
    Route::get('/coins/hour', 'CoinsController@getHourCoins');
    // Ajax処理：過去1日のツイート数を取得するエンドポイント
    Route::get('/coins/day', 'CoinsController@getDayCoins');
    // Ajax処理：1時間のスイート数を取得するエンドポイント
    Route::get('/coins/week', 'CoinsController@getWeekCoins');

    /*****************************************
     * プロフィール機能関連のルーティング
     *****************************************/
    Route::get('/mypage', 'MypageController@index')->name('mypage.index');
    // Ajax処理：ユーザーデータの取得
    Route::get('/mypage/user', 'MypageController@getUserData');
    // Ajax処理：ユーザーデータやパスワードの新規登録
    Route::post('/mypage/userdata', 'MypageController@storUserData');
    // Ajax処理：Twitterユーザーの連携解除
    Route::post('/mypage/clear-twuser', 'MypageController@clearTwitterAuth');
    // Ajax処理：退会処理
    Route::post('/mypage/delete', 'MypageController@delete');
});
