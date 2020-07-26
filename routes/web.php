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

// 認証系のルーティングそれに対応するコントローラがまとまっている
// Illuminate\Routing\Routerクラスのauth()メソッドにルーティングが記述されている
 // email認証の機能を有効化

// トップページのページの表示
Route::get('/', 'IndexController@home')->name('home');


// Twitter経由でのログインを行う為のURI
Route::get('login/twitter', 'Auth\TwitterAuthController@getTwitterLogin')->name('twitter.login');
// Twitter経由でのユーザー登録を行う為のURI
Route::get('register/twitter', 'Auth\TwitterAuthController@getTwitterRegister')->name('twitter.register');
// アプリ側から情報が返ってくるURL
Route::get('auth/twitter/callback', 'Auth\TwitterAuthController@getTwitterCallback');
// ログアウト
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

// // お問い合わせページの表示
// Route::get('/contact', 'IndexController@contact')->name('contact.index');
// // お問い合わせ内容の送信
// Route::post('/contact/confirm', 'IndexController@confirm')->name('contact.confirm');

Auth::routes();

// 認証後の画面
Route::group(['middleware' => 'auth'], function () {

    // 仮想通貨関連のニュースの取得
    Route::get('/news', 'NewsController@index')->name('getNews.index');

    /**********************************************
     * ユーザーフォロー機能関連のルーティング
     **********************************************/
    // 仮想通貨関連のTwitterユーザーページを表示
    Route::get('/tweet-users', 'TwitterController@index')->name('userList.index');
    // Ajax処理：ユーザーをフォローする
    Route::post('/follow', 'FollowController@follow');
    // // Ajax処理：フォローを外す
    Route::post('/unfollow', 'FollowController@unfollow');
    // Ajax処理：自動フォロー機能をONにする
    Route::post('/autofollow', 'FollowController@autoFollowFlg');
    // Twitter未登録ユーザーをTwitterアカウント登録へ遷移させるための処理
    Route::get('/userlist/redirect', 'FollowController@registerRedirect')->name('userList.redirect');

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
    // Ajax処理：退会処理
    Route::post('/mypage/delete', 'MypageController@delete');
});

// 開発時テスト用ルーティング
// Route::get('/applimit', 'TestController@applimit'); // アプリケーション認証のAPI制限のカウント数の一覧を取得する
// Route::get('/userlimit', 'TestController@userlimit'); // ログインしているユーザーのAPI制限のカウント数の一覧を取得する
