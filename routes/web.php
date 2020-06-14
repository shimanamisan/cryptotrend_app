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

Route::get('/', function () {
    return view('home');
})->name('home');

// 認証系のルーティングそれに対応するコントローラがまとまっている
// Illuminate\Routing\Routerクラスのauth()メソッドにルーティングが記述されている
Auth::routes();

// プロフィール画面
Route::get('/profile', 'ProfileController@showProfileForm')->name(
    'profile.showProfileForm'
);
Route::patch('/profile/{id}', 'ProfileController@editProfile')->name(
    'profile.editProfile'
);

// Twitter経由でのログインを行う為のURI
Route::get(
    'login/twitter',
    'Auth\LoginController@redirectToTwitterProvider'
)->name('twitter.auth');
// アプリ側から情報が返ってくるURL
Route::get(
    'login/twitter/callback',
    'Auth\LoginController@handleTwitterCallback'
)->name('twitter.callback');

// 仮想通貨関連のニュースの取得
Route::get('/news', 'NewsController@index')->name('getNews.index');

// 仮想通貨関連のTwitterユーザーを取得
Route::get('/tweet-users', 'TwitterController@index')->name('userList.index');
Route::get('/test', 'TwitterController@userList')->name('userList.test');

// タイムラインの取得
Route::get('/get-timeline', 'TwitterController@searchTweet')->name(
    'searchTweet'
);

// ログイン後の画面
Route::get('/coins', 'CoinsController@index')->name('conins.index');

// ログアウト
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
