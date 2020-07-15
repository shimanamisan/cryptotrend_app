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
        $user = User::find($request->id);
        $data = $request->all();

        // パスワードが新規登録の場合
        if (empty($user->password)) {
            
            // リクエストフォームから受け取ったパスワードをハッシュ化
            $newPass = Hash::make($request->password);
            $user->password = $newPass;
            $user->save();

            \Log::debug('パスワードを新規登録しました。');

            return response()->json(['user' => $user, 'success' => 'パスワードを登録しました。']);
        } else {

            // パスワード更新時の処理
            // DBに登録されているハッシュ化されたパスワードと入力されたパスワードが一致するか確認
            if (Hash::check($request->password, $user->password)) { // 第一引数にプレーンパスワード、第二引数にハッシュ化されたパスワード
                //
                \Log::debug('登録されているパスワードと同じでした。');
                return response()->json(['user' => $user, 'success' => 'パスワードを変更しました。']);
            } else {
                // DBと違っていればパスワードを更新する
                // リクエストフォームから受け取ったパスワードをハッシュ化
                $newPass = Hash::make($request->password);
                $user->password = $newPass;
                $user->save();
                \Log::debug('パスワードを更新しました');

                return response()->json(['user' => $user, 'success' => 'パスワードを変更しました。']);
            }
        }
    }

    // 退会処理
    public function delete()
    {

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
        // ログイン保持のトークンを空にする
        $userInfo->remember_token = null;
        // ステータスを保存する
        $userInfo->save();
        // セッションを削除
        session()->invalidate();
        // csrfトークンを再生成
        session()->regenerateToken();
    
        return response()->json(['success'], 200);
    }
}
