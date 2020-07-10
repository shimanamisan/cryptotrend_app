<?php

namespace App\Http\Controllers;

use App\Twuser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Carbon; // ★ 追加
use Illuminate\Support\Facades\DB; // ★追加

class TwitterController extends Controller
{

    public function __construct()
    {
        // $this->middleware(['auth','verified']);
        $this->middleware('auth');
    }

    public function index()
    {
        // 認証されたユーザー情報を取得
        $outh_user = Auth::user();
        // JSON形式へ変換
        $user = json_encode($outh_user);
        // 新しく登録されたアカウントから表示していく
        $result = Twuser::orderBy('id', 'desc')->get();
        // 取得した情報をJSON形式へ変換
        $tw_user = json_encode($result);

        // return view('userList', ['tw_user' => $tw_user]);
        return view('userList', compact('tw_user', 'user'));
    }

    // アプリケーション単位で認証する（ベアラートークンの取得）
    private function twitterOauth2()
    {
        // ヘルパー関数のconfigメソッドを通じて、config/services.phpのtwitterトークン用の設定を参照
        $config = config('services.twitter');
        // アプリ認証参考
        // https://qiita.com/yasunori_tanochi_gp/items/2e238638f846a1b1240f
        $api_key = $config['client_id'];
        $api_key_secret = $config['client_secret'];
        $access_token = $config['access_token'];
        $access_token_secret = $config['access_token_secret'];
        // インスタンスを生成
        $connection  = new TwitterOAuth($api_key, $api_key_secret, $access_token, $access_token_secret);

        // アプリ認証用のベアラートークンを取得
        $_bearer_token = $connection->oauth2('oauth2/token', array('grant_type' => 'client_credentials'));

        // ベアラートークンをセット
        if(isset($_bearer_token->access_token)){
            $connection->setBearer($_bearer_token->access_token);
        }

        return $connection;
    }

    // ユーザー認証用のメソッド
    public function twitterAuth()
    {
        // ヘルパー関数のconfigメソッドを通じて、config/services.phpのtwitterの登録した中身を参照
        $config = config('services.twitter');
        // APIキーを格納
        $api_key = $config['client_id'];
        $api_key_secret = $config['client_secret'];
        // アクセストークンを格納
        $access_token = session('access_token');
        $access_token_secret = session('access_token_secret');

        $OAuth = new TwitterOAuth($api_key, $api_key_secret, $access_token, $access_token_secret);

        return $OAuth;
    }
}
