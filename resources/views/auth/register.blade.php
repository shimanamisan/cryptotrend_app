@extends('layouts.app')

@section('title', 'CryptoTrend | 新規登録')
@section('description', '仮想通貨トレンド検索サービス')
@include('layouts.head')

@section('content')
<main class="l-main l-main__auth">
      <div class="c-container__auth">
        <div class="p-form__title">
          新規登録する
        </div>
        @if(Session::has('error_message'))
        <div class="c-error__authflash">
            <p>{{ session('error_message') }}</p>
        </div>
        @endif
        <hr class="u-line" />
        <div class="p-form__body">
          <form method="POST" action="">
          @csrf
            <label class="p-form__info" for="name">ニックネーム</label>
            <input
              class="c-form__input c-from__input--login @error('name') c-error__input @enderror"
              type="text"
              name="name"
              value="{{ old('name') }}"
            />
            @error('name')
            <div class="c-error">
              {{ $message }}
            </div>
            @enderror
            <label class="p-form__info" for="email">メールアドレス</label>
            <input
              class="c-form__input c-from__input--signup @error('email') c-error__input @enderror"
              type="text"
              name="email"
              value="{{ old('email') }}"
            />
            @error('email')
            <div class="c-error">
              {{ $message }}
            </div>
            @enderror
            <label class="p-form__info" for="password">パスワード</label>
            <input
              class="c-form__input c-from__input--signup @error('password') c-error__input @enderror"
              type="password"
              name="password"
              value="{{ old('password') }}"
            />
            <span class="p-form__info--pass">※半角英数8文字以上で入力して下さい</span>
            @error('password')
            <div class="c-error">
              {{ $message }}
            </div>
            @enderror
            <label class="p-form__info" for="password-confirm">パスワード再入力</label>
            <input
              id="password-confirm"
              class="c-form__input c-from__input--signup @error('password_confirmation') c-error__input @enderror"
              type="password"
              name="password_confirmation"
             
            />
            <div class="u-wrapp">
              <button
                class="c-btn c-btn__auth"
                type="submit"
                >メールアドレスで新規登録
            </button>
            </div>
          </form>
          <hr class="u-line" />
          <span class="u-line--or">または</span>
          <div class="u-wrapp">
              <a href="{{ route('twitter.register') }}" class="c-btn c-btn__twitter">Twitterで新規登録</a>
          </div>
          <!-- <div class="p-form__signup--explanation">
            本サービスを登録することにより、<a href="" class="c-link--global"
              >利用規約</a
            >と<a href="" class="c-link--global">プライバシーポリシー</a
            >に同意したとみなされます。
          </div> -->
        </div>
      </div>
      <div class="p-form__unit">
        <a class="c-link--global" href="{{ route('login') }}"
          >＜既に登録済みの方はこちら</a
        >
      </div>
    </main>
@endsection
