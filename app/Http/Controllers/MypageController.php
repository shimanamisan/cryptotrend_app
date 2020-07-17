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

        // SNS登録後でパスワードが空だった場合
        if (empty($user->password)) {
            return $user;
        } else {

            // 登録されていた場合のプロパティを追加する
            $user['isset_pass'] = true;
            return $user;
        }
    }
    // ユーザーデータの更新処理
    public function storUserData(MypageRequest $request)
    {
        // $user = User::where('id', $request->id)->where('delete_flg', 0)->first();
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

            // パスワードが新規登録の場合
            if (empty($user->password)) {
            
            // リクエストフォームから受け取ったパスワードをハッシュ化
                $newPass = Hash::make($request->password);
                $user->password = $newPass;
                $user->save();

                \Log::debug('パスワードを新規登録しました。');
                \Log::debug('   ');
            } else {

                // パスワード更新時の処理
                // DBに登録されているハッシュ化されたパスワードと入力されたパスワードが一致するか確認
                if (Hash::check($request->password, $user->password)) { // 第一引数にプレーンパスワード、第二引数にハッシュ化されたパスワード
                    //
                    \Log::debug('登録されているパスワードと同じでした。');
                    \Log::debug('   ');
                } else {
                    // DBと違っていればパスワードを更新する
                    // リクエストフォームから受け取ったパスワードをハッシュ化
                    $newPass = Hash::make($request->password);
                    $user->password = $newPass;
                    $user->save();
                    \Log::debug('パスワードを更新しました');
                    \Log::debug('   ');
                }
            }
        } catch (\Exception $e) {
            \Log::debug('アカウント情報変更時に例外が発生しました。' .$e->getMessage());
            \Log::debug('   ');
            return response()->json(['error', 'エラーが発生しました。'], 500);
        }

        return response()->json(['user' => $user, 'success' => 'アカウント情報を更新しました。']);
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
            \Session::flash('withdraw_message', '退会しました。ご利用ありがとうございました。');
        
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
