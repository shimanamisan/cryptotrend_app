@extends('layouts.app')

@section('title', 'CryptoTrend | 仮想通貨トレンド')
@section('description', '仮想通貨トレンド検索サービス')
@include('layouts.head')

@section('content')
<main class="l-main l-main__auth l-main__auth--reminder">
      <div class="c-container__auth">
        <div class="p-form__title">
          パスワード再設定
        </div>
        <hr class="u-line" />
        <div class="p-form__body">
          <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <label class="p-form__info p-form__info--reminder" for="email"
              >メールアドレス</label
            >
            <input
                id="email"
                class="c-form__input c-from__input--reminder @error('email') c-error__input @enderror"
                type="text"
                name="email"
                value="{{ old('email') }}"
            />
            @error('email')
            <div class="c-error">
             {{ $message }}
            </div>
            @enderror
            <label class="p-form__info p-form__info--reminder" for="password"
              >パスワード</label
            >
            <input
            id="password"
              class="c-form__input c-from__input--reminder @error('password') c-error__input @enderror"
              type="password"
              name="password"
            />
            @error('password')
            <div class="c-error">
             {{ $message }}
            </div>
            @enderror
            <label class="p-form__info p-form__info--reminder" for="password-confirm"
              >パスワード再確認</label
            >
            <input
                id="password-confirm"
                class="c-form__input c-from__input--reminder"
                type="password"
                name="password_confirmation"
            />

            <div class="u-wrapp">
              <button class="c-btn c-btn__login" type="submit">
                パスワードを変更
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
@endsection
