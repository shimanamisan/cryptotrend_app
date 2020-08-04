<?php

namespace App\Http\Controllers;

use App\User; // ★追加
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // ★追加
use Illuminate\Support\Facades\Log; // ★追加
use App\Http\Requests\MypageRequest; // ★追加
use Illuminate\Support\Facades\Auth; // ★追加

class MypageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('mypage');
    }

    public function getUserData()
    {
        // Ajax処理でユーザー情報を取得
        $user = Auth::user();

        // ログイン中のユーザー情報を返す
        return $user;
    }
    // ユーザーデータの更新処理
    public function storUserData(MypageRequest $request)
    {
        try {
            $user = Auth::user();
            
            $data = $request->all();

            // ニックネームが変更されていた場合
            if ($user->name !== $data['name']) {
                $user->name = $data['name'];
                $user->save();
                \Log::debug('ニックネームを更新しました');
                \Log::debug('   ');
            }

            // メールアドレスが変更されていた場合
            if ($user->email !== $data['email']) {
                $user->email = $data['email'];
                $user->save();
                \Log::debug('メールアドレスを更新しました');
                \Log::debug('   ');
            }
            
            // 現在のパスワードをチェック
            if (!(Hash::check($request->get('old_password'), Auth::user()->password))) {
                \Log::debug('現在のパスワードが違います');
                \Log::debug('   ');
                
                $errors = ['errors' =>
                    ['old_password' =>
                        ['現在のパスワードが違います。']
                    ]
                ];
                // ステータスコードとエラーメッセージを返す
                return response()->json($errors, 422);
            }

            // パスワード更新時の処理
            // DBに登録されているハッシュ化されたパスワードと入力されたパスワードが一致するか確認
            if (Hash::check($request->password, $user->password)) { // 第一引数にプレーンパスワード、第二引数にハッシュ化されたパスワード
                // パスワードがDBのものと同じ場合は、違うパスワードを設定するようにメッセージを出す。
                \Log::debug('登録されているパスワードと同じでした。');
                \Log::debug('   ');

                $errors = ['errors' =>
                    ['password' =>
                        ['前回と違うパスワードを設定して下さい。']
                    ]
                ];
                // ステータスコードとエラーメッセージを返す
                return response()->json($errors, 422);
            } else {
                // DBと違っていればパスワードを更新する
                // リクエストフォームから受け取ったパスワードをハッシュ化
                $newPass = Hash::make($request->password);
                $user->password = $newPass;
                $user->save();
                \Log::debug('パスワードを更新しました');
                \Log::debug('   ');
            }
        } catch (\Exception $e) {
            \Log::debug('アカウント情報変更時に例外が発生しました。' .$e->getMessage());
            \Log::debug('   ');
            return response()->json(['error', 'エラーが発生しました。'], 500);
        }

        return response()->json(['user' => $user, 'success' => 'アカウント情報を更新しました。']);
    }

    // Twitter連携を解除する
    public function clearTwitterAuth()
    {
        try {
            $user = Auth::user();
            // followsテーブルにあるフォロー済みユーザーを削除する
            $user->follows()->delete();
            // ユーザーアカウントに登録されていたTwitterIDなどを削除する
            $user->my_twitter_id = null;
            $user->twitter_token = null;
            $user->twitter_token_secret = null;
            $user->save();

            return response()->json(['user' => $user, 'success' => 'Twitter連携を解除しました。']);
        } catch (\Exception $e) {
            \Log::debug('アカウント情報変更時に例外が発生しました。' .$e->getMessage());
            \Log::debug('   ');
        }
    }

    // 退会処理
    public function delete()
    {
        try {
            // 認証済みユーザーを取得
            $user = Auth::user();
            // ログアウト
            Auth::logout();
            
            // delete_flgが立っていないユーザーを検索
            $userInfo = User::where('email', $user->email)
                ->where('delete_flg', 0)
                ->first();
            // デリートフラグを立てる
            $userInfo->delete_flg = 1;
            // followsテーブルのdelete_flgカラムも更新する
            User::find($user->id)->follows()->update(['delete_flg' => 1]);
            // ログイン保持のトークンを空にする
            $userInfo->remember_token = null;
            // ステータスを保存する
            $userInfo->save();
            // セッションを削除
            session()->invalidate();
            // csrfトークンを再生成
            session()->regenerateToken();
            // 退会後のフラッシュメッセージを格納
            \Session::flash('system_message', '退会しました。ご利用ありがとうございました。');
        
            return response()->json(['success'], 200);
        } catch (\Exception $e) {

            // ログアウト
            Auth::logout();
            // セッションを削除
            session()->invalidate();
            // csrfトークンを再生成
            session()->regenerateToken();
            \Log::debug('退会処理時に例外が発生しました。'. $e->getMessage());

            return response()->json(['error', 'エラーが発生しました。'], 500);
        }
    }
}
