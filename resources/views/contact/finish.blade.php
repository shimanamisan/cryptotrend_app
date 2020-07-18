@extends('layouts.app')

@section('title', 'CryptoTrend | 新規登録')
@section('description', '仮想通貨トレンド検索サービス')
@include('layouts.head')

@section('content')
<main class="l-main l-main__auth">
      <div class="c-container__auth c-container__auth__contact">
        <div class="p-form__title">
          お問い合わせ
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
            <label class="p-form__info" for="subject">件名</label>
            <input
              class="c-form__input c-from__input--login @error('subject') c-error__input @enderror"
              type="text"
              name="subject"
              value="{{ old('subject') }}"
            />
            @error('subject')
            <div class="c-error">
              {{ $message }}
            </div>
            @enderror

            <label class="p-form__info" for="name">お名前</label>
            <input
              class="c-form__input c-from__input--signup @error('name') c-error__input @enderror"
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

            <label class="p-form__info" for="contact">お問い合わせ内容</label>
            <textarea id="conatct-message" name="contact" cols="30" rows="8" class="c-form__input p-form__contact" >{{old('contact')}}</textarea>
            <p class="c-form__contact--text">0/1000文字以内</p>
            <!-- <input
              class="c-form__input c-from__input--signup @error('email') c-error__input @enderror"
              type="text"
              name="email"
              value="{{ old('email') }}"
            /> -->
            @error('email')
            <div class="c-error">
              {{ $message }}
            </div>
            @enderror
       
            <div class="u-wrapp">
              <button
                class="c-btn c-btn__auth"
                type="submit"
                >内容の確認
            </button>
            </div>
          </form>
       
        </div>
      </div>
     
    </main>
@endsection
