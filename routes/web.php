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

// Twitter経由でのログインを行う為のURI
Route::get('login/twitter', 'Auth\TwitterAuthController@getTwitterLogin')->name('twitter.login');
// Twitter経由でのユーザー登録を行う為のURI
Route::get('register/twitter', 'Auth\TwitterAuthController@getTwitterRegister')->name('twitter.register');
// アプリ側から情報が返ってくるURL
Route::get('auth/twitter/callback', 'Auth\TwitterAuthController@getTwitterCallback');
// Twitterアカウントて新規登録する処理

// 仮想通貨関連のニュースの取得
Route::get('/news', 'NewsController@index')->name('getNews.index');

// プロフィール画面
Route::patch('/profile/{id}', 'ProfileController@editProfile')->name('profile.editProfile');
Route::get('/profile', 'ProfileController@showProfileForm')->name('profile.showProfileForm');

// ログアウト
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware' => 'auth'], function () {
    // 認証後の画面
    // 仮想通貨関連のTwitterユーザーを取得
    Route::get('/tweet-users', 'TwitterController@index')->name('userList.index');
    // ユーザーをフォローする
    Route::post('/follow', 'FollowController@follow');
    // 自動フォロー機能をONにする
    Route::post('/autofollow', 'FollowController@autoFollowFlg');
    // 仮想通貨情報のページ
    Route::get('/coins', 'CoinsController@index')->name('conins.index');
});

// 開発時テスト用ルーティング
Route::get('/1', 'CoinsController@hour'); // 仮想通貨関連のツイート数を取得する（完成後バッチ処理にする）
Route::get('/2', 'CoinsController@day'); // 仮想通貨関連のツイート数を取得する（完成後バッチ処理にする）
Route::get('/3', 'CoinsController@week'); // 仮想通貨関連のツイート数を取得する（完成後バッチ処理にする）
Route::get('/testuserList', 'TwitterController@userList'); // 仮想通貨関連のツイートをしているユーザーを取得する（完成後バッチ処理にする）
Route::get('/testautoFollow', 'FollowController@handl'); // 過疎通過関連のユーザーを自動フォローする（完成後バッチ処理にする）
Route::get('/testlimit', 'TwitterController@limit'); // ログインしているユーザーのAPI制限のカウント数の一覧を取得する
