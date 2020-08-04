<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Validation\Rule; // ★追加
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        // Twitter認証のログイン処理時に、ログイン・新規登録判定用のセッションを
        // 新規登録画面表示時に、残っていないようにする。
        session()->forget('login_flg');

        return view('auth.register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // カスタムエラーメッセージ
        $message = [
            'email.unique' => '無効なメールアドレスです。メールアドレスを確認してやり直してください。',
            'regex' => '半角英数のみご利用いただけます。',
            'confirmed' => ':attributeと、:attribute再入力が一致していません。',
        ];

        return Validator::make($data, [
            'name' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:100',
                        // ユーザーテーブルのdelete_flgが0のユーザーに対してemailの同値チェックを行う
                        Rule::unique('users', 'email')->where('delete_flg', 0)],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed', 'regex:/^[a-zA-Z0-9]+$/'],
        ], $message);
    }
    
    // RedirectsUsersトレイトのredirectPathメソッドを上書き
    public function redirectPath()
    {
        return '/mypage';
        //例）return 'costs/index';
    }
    
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
