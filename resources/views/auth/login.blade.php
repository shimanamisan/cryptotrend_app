@extends('layouts.app')

@section('title', 'CryptoTrend | ログイン')
@section('description', '仮想通貨トレンド検索サービス')
@include('layouts.head')

@section('content')
<main class="l-main l-main__auth">
      <div class="c-container__auth">
        <div class="p-form__title">
          ログインする
        </div>
        <hr class="u-line" />
        <div class="p-form__body">
          <form method="POST" action="{{ route('login') }}">
          @csrf
            <label class="p-form__info" for="email">メールアドレス</label>
            <input
              class="c-form__input c-from__input--login @error('email') c-error__input @enderror"
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
              class="c-form__input c-from__input--login @error('password') c-error__input @enderror"
              type="password"
              name="password"
              value="{{ old('password') }}"
            />
            @error('password')
            <div class="c-error">
              {{ $message }}
            </div>
            @enderror
            <a class="p-form__inquiry" href="{{ route('password.request') }}"
              ><span>パスワードをお忘れですか？</span></a
            >
            <div class="u-wrapp">
              <input
                class="c-btn c-btn__login"
                type="submit"
                value="ログイン"
              />
            </div>
          </form>
          <hr class="u-line" />
          <span class="u-line--or">または</span>
          <div class="u-wrapp">
            <button class="c-btn c-btn__twitter">
              <a href="{{ route('twitter.auth') }}">Twitterで登録・ログイン</a>
            </button>
          </div>
        </div>
      </div>
    </main>
@endsection
