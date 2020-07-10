<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;

class TestController extends Controller
{
    /**
     * これはテスト用の処理
     */
    // アプリケーションのリミットを取得
    public function applimit()
    {
        $connection = $this->twitterOauth2();
      
        $search_result = $connection->get('application/rate_limit_status');

        dd($search_result->resources);
    }

    // ユーザーのリミット数を取得する
    public function userlimit()
    {
        $connection = $this->twitterAuth();
      
        $search_result = $connection->get('application/rate_limit_status');

        dd($search_result->resources);
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
        if (isset($_bearer_token->access_token)) {
            $connection->setBearer($_bearer_token->access_token);
        }
  
        return $connection;
    }
}
