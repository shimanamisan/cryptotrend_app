<?php

namespace App\Http\Controllers;

use App\Follow; // ★追加
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
        $this->middleware('auth');
    }

    // テンプレートを表示する
    public function index()
    {
        // 認証されたユーザー情報を取得
        $outh_user = Auth::user();
        // JSON形式へ変換
        $user = json_encode($outh_user);
        // 新しく登録されたアカウントから表示していく
        $tw_user_result = Twuser::orderBy('id', 'desc')->get();
        // dd($tw_user_result);
        // フォローテーブルのデータを取得
        $follow_list_result = Follow::where('user_id', Auth::user()->id)->where('delete_flg', 0)->get();
        // dd($follow_list_result);
        
        // followsテーブルからのコレクションのオブジェクトが空で無ければ処理を実行
        if ($follow_list_result->isNotEmpty()) {
            // 仮想通貨関連一覧ユーザーとフォロー済みのいユーザーのデータを比較
            // フォローしているユーザーに isFollow プロパティを追加する
            foreach ($tw_user_result as $tw_item) {
                // dd(gettype($tw_item->id)); // String
                $tw_ID = (int)$tw_item->id; // 数値型に変換
                
                foreach ($follow_list_result as $follow_item) {
                    if ($tw_ID === $follow_item->twuser_id) {
                        $tw_item['isFollow'] = true;
                        // モデルを配列に変換して、新しい配列へ格納
                        $new_tw_user[] = $tw_item->toArray();
                    }
                }
            }
            // 結合先
                $source = $tw_user_result->toArray(); // コレクションを配列へ変換
                // 結合元(destinationをsourceに結合する)
                $destination = $new_tw_user;
            // 配列の差分を結合する、既存のデータに isFollow = true が追加されたデータが取得できる
            $result = array_merge($source, $destination);
            
            $follow_list = json_encode($source);
        } else {
            // 空だった場合はまだフォローしているユーザーがまだいないので、アプリ側で登録したユーザーをそのまま表示する
            $source = $tw_user_result;

            $follow_list = json_encode($source);
        }

        return view('userlist', compact('follow_list', 'user'));
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
