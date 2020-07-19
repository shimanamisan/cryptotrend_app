@extends('layouts.app')

@section('title', 'CryptoTrend | 仮想通貨トレンド')
@section('description', '仮想通貨トレンド検索サービス')
@include('layouts.head')

@section('content')
<main class="l-main l-main__auth l-main__auth--reminder u-padding__top--lg">
      <div class="c-container__auth">
        @if (session('status'))
            <div class="c-msg c-msg__send" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="p-form__title">
          パスワード再設定
        </div>
        <hr class="u-line" />
        <div class="p-form__body">
          <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label class="p-form__info p-form__info--reminder" for="email"
              >ご登録のメールアドレスを入力してください</label
            >
            <input
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
            <div class="u-wrapp">
              <button
                class="c-btn c-btn__auth"
                type="submit"
              >送信する</button>
            </div>
          </form>
        </div>
      </div>
    </main>
@endsection
