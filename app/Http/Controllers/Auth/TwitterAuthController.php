<?php

namespace App\Http\Controllers\Auth;

use App\User; // ★追加
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth; // ★追加
use Laravel\Socialite\Facades\Socialite; // ★追加
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class TwitterAuthController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('guest')->except('logout');
  }

   /*********************************************************
   * Twitterログイン・新規登録
   *********************************************************/
  // Twitter認証済みで、ログインするときの処理
  public function getTwitterLogin()
  {
    // ログインの処理であることを判断するためにセッションにフラグを入れる
    session(['login_flg' => true]);
    return Socialite::driver('twitter')->redirect();
  }

  // 初回Twitter認証時の処理
  public function getTwitterRegister()
  {
    // 初回登録時の処理
    return Socialite::driver('twitter')->redirect();
  }

  // Twitter認証ページからリダイレクトを受け取り、レスポンスデータを元に新規登録するか否か決定する
  public function getTwitterCallback()
  {

    try {
        // ユーザーデータの取得とアクセストークンの取得
        $user = Socialite::driver('twitter')->user();
        
            // ログイン画面からリダイレクトしてきたときの処理
            if(session()->has('login_flg')){
                \Log::debug('ログイン時の処理です');
                \Log::debug('   ');
                
                // メールアドレスが未登録の場合は、ユーザー登録していないユーザーと判定する
                $userInfo = User::where('email', $user->getEmail())->first();
                // twitter_idも格納
                $twserId = User::where('my_twitter_id', $user->getId())->first();
                    
                    // Twitter_id及びメールアドレスが登録されていなかったら未登録ユーザーとする
                    if( empty($twUserId) || empty($userInfo) ){
                        \Log::debug('emailが無いのでなので未登録ユーザーです');
                        \Log::debug('   ');
                        // 画面遷移する前にログインフラグを削除
                        session()->forget('login_flg');
                        // ログイン画面へリダイレクト
                        return redirect('/register')->with('error_message', '提供された資格情報を持つアカウントは見つかりませんでした。新規登録を行って下さい。');

                    }

                // emailでユーザー情報が登録されていた場合は、TwitterIDなどをDBへ格納する
                $userInfo->fill([
                    'my_twitter_id' => $user->getId(),
                    'twitter_token' => $user->token,
                    'twitter_token_secret' => $user->tokenSecret,
                ]);

                dd($userInfo);

                $userInfo->save();

                \Log::debug('認証に成功しました');
                \Log::debug('   ');
            
                Auth::login($userInfo);
                // 画面遷移する前にログインフラグを削除
                session()->forget('login_flg');
                // トレンド一覧画面へリダイレクト
                \Log::debug(session()->all());
                return redirect()->to('/coins');
    
            }else{

                // login_flgが空だったら新規登録画面からリダイレクトしてきたものと判定
                \Log::debug('新規登録画面の処理です');
                \Log::debug('   ');

                // 新規登録時の登録処理を関数に切り出し
                $userInfo = $this->findCreateUser($user);
                
                // セッションにTwitterユーザー情報を入れる
                session(['twitter_id' => $user->id]);
                session(['access_token' => $user->token]);
                session(['access_token_secret' => $user->tokenSecret]);
                session(['follow_limit_time' => $userInfo->follow_limit_time]);
                \Log::debug('新規登録に成功しました。セッションを格納し、画面遷移します');
                \Log::debug('   ');
    
                Auth::login($userInfo);

                // 画面遷移する前にログインフラグを削除
                session()->forget('login_flg');
                // トレンド一覧画面へリダイレクト
                return redirect()->to('/coins');
        }
   
    } catch (\Exception $e) {
      \Log::debug('ログインに失敗しました。例外の処理に入っています。');
      \Log::debug('   ');
      // 画面遷移する前にログインフラグを削除
      session()->forget('login_flg');
      // エラーならログイン画面へリダイレクト
      return redirect('/login')->with('error_message', 'ログインに失敗しました。');
    }
  }

  // 
  private function findCreateUser($user)
    {
        $validUser = User::where('email', $user->getEmail())->first();

        // メールアドレスで登録済みのユーザーで、新規でTwitter認証をしてきている場合
        if(!empty($validUser)){

            $validUser->fill([    
                'my_twitter_id' => $user->getId(),
                'twitter_token' => $user->token,
                'twitter_token_secret' => $user->tokenSecret,
            ]);

            $validUser->save();
            return $validUser;
        }

        // 未登録ユーザーだったときの処理
        $newUser = User::create([
            'name' => $user->getNickname(),
            'email' => $user->getEmail(),
            'my_twitter_id' => $user->getId(),
            'twitter_token' => $user->token,
            'twitter_token_secret' => $user->tokenSecret,
        ]);

        return $newUser;

    }
}
