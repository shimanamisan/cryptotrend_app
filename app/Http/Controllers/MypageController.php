<?php

namespace App\Http\Controllers;

use App\User; // ★追加
use App\EmailReset; // ★追加
use Illuminate\Http\Request;
use Carbon\Carbon; // ★追加
use Illuminate\Support\Str; // ★追加
use Illuminate\Support\Facades\Log; // ★追加
use App\Http\Requests\MypageRequest; // ★追加
use Illuminate\Support\Facades\Auth; // ★追加
use Illuminate\Support\Facades\Hash; // ★追加
use App\Http\Requests\MypagePasswordRequest; // ★追加
use App\Http\Requests\MypageUserDataRequest; // ★追加

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
    public function storUserData(MypageUserDataRequest $request)
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
                // $user->email = $data['email'];
                // $user->save();
                // \Log::debug('メールアドレスを更新しました');
                // \Log::debug('   ');
                \Log::debug('メールアドレス変更通知を送信します。');
                \Log::debug('   ');

                $this->sendChangeEmailLink($data['email']);

                return response()->json(['success' => 'メールアドレス変更通知を送信しました。受信ボックスを確認してください。']);
            }
        } catch (\Exception $e) {
            \Log::debug('アカウント情報変更時に例外が発生しました。' .$e->getMessage());
            \Log::debug('   ');
            return response()->json(['error', 'エラーが発生しました。'], 500);
        }

        return response()->json(['user' => $user, 'success' => 'アカウント情報を更新しました。']);
    }
    // パスワードの更新処理
    public function changePasswordData(MypagePasswordRequest $request)
    {
        try {
            $user = Auth::user();
            
            $data = $request->all();
            
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

        return response()->json(['user' => $user, 'success' => 'パスワードを変更しました。']);
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

            session()->forget('twitter_id');
            session()->forget('access_tokena');
            session()->forget('access_token_secret');

            \Log::debug('Twitterアカウントの連携を解除しました。');
            \Log::debug('   ');

            return response()->json(['success' => 'Twitter連携を解除しました。']);
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
            // 自動フォローステータスをOFFにする
            $userInfo->autofollow_status = 0;
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

    // メールアドレス変更リンクを送信する
    public function sendChangeEmailLink($email)
    {
        // トークン生成
        $token = hash_hmac('sha256', Str::random(40) . $email, config('app.key'));

        // トークンをDBへ保存
        $param = [];
        $param['user_id'] = Auth::id();
        $param['new_email'] = $email;
        $param['token'] = $token;
        // 新しいレコードを作成
        $email_reset = EmailReset::create($param);
        // リセットメールを送信する
        $email_reset->sendEmailResetNotification($token);

        \Log::debug('メールアドレス変更確認メールを送信しました。');
        \Log::debug('   ');
    }

    public function changeEmail(Request $request, EmailReset $email_reset, $token)
    {
        // トークンが登録されているものか確認
        $userEmail = $email_reset->where('token', $token)->first();
        \Log::debug('URLクエリから渡ってきたトークンがDBに保存されているかチェックしています。');
        \Log::debug('   ');

        // トークンが存在している且つ、有効期限が切れていないかチェック
        if ($userEmail && !$this->tokenExpired($userEmail->created_at)) {
            \Log::debug('トークンが存在しており、有効期限以内でした！');
            \Log::debug('   ');
            // ユーザーのメールアドレスを変更
            $user = User::find($userEmail->user_id);
            $user->email = $userEmail->new_email;
            $user->save();
            // 登録後は、変更に使用したトークンやユーザーID、メールアドレスが格納されたレコードを削除する
            $userEmail->delete();
            \Log::debug('ユーザーのメールアドレスを変更して、email_resetsテーブルのレコードを削除しました。');
            \Log::debug('   ');

            return redirect('/mypage')->with('system_message', 'メールアドレスを更新しました。');
        } else {
            // レコードが存在していて、有効期限が切れていた場合削除
            if ($userEmail) {
                $email_reset->where('token', $token)->delete();
            }

            return redirect('/mypage')->with('system_message', 'メールアドレスの更新に失敗しました。');
        }
    }

    protected function tokenExpired($createdAt)
    {
        // トークンの有効期限は30分に設定
        $expires = 60 * 30;
        // parseメソッドで、サーバー内の時刻ではなく現在時刻を指定した値にする。ここでは、DBに登録された created_atの時刻が指定される
        // そこから30分が経過しているか判定する。
        // addSecondsメソッドで、日付を加算する。60 * 30 が入っている
        // isPastメソッドで、指定された時刻より過去かどうか判定する。進んでいればfalseが返ってくる
        return Carbon::parse($createdAt)->addSeconds($expires)->isPast();
    }
}
