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
      @if(Session::has('error_message'))
        <div class="c-error__authflash">
            <p>{{ session('error_message') }}</p>
        </div>
      @endif
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
           
            <div class="p-form__info--save">
                    <label>
                        <input type="checkbox" name="remember">
                        ログイン情報を保持する
                    </label>
            </div>
            <div class="u-wrapp">
              <button
                class="c-btn c-btn__auth"
                type="submit"
                
              >ログイン</button>
            </div>
            <a class="p-form__inquiry" href="{{ route('password.request') }}"
              ><span>パスワードをお忘れですか？</span></a
            >
          </form>
          <!-- <hr class="u-line" />
          <span class="u-line--or">または</span>
          <div class="u-wrapp">
         
              <a href="{{ route('twitter.login') }}" class="c-btn c-btn__twitter">Twitterでログイン</a>
        
          </div> -->
        </div>
      </div>
    </main>
@endsection
