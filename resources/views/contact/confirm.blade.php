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
            <p>{{ $form_value['subject'] }}</p>
            <input
              class="c-form__input c-from__input--login"
              type="hidden"
              name="subject"
              value="{{ $form_value['subject'] }}"
            />

            <label class="p-form__info" for="name">お名前</label>
            <p>{{ $form_value['name'] }}</p>
            <input
              class="c-form__input c-from__input--signup"
              type="hidden"
              name="name"
              value="{{ $form_value['name'] }}"
            />
         
            <label class="p-form__info" for="email">メールアドレス</label>
            <p>{{ $form_value['email'] }}</p>
            <input
              class="c-form__input c-from__input--signup"
              type="hidden"
              name="email"
              value="{{ $form_value['email'] }}"
            />
    
            <label class="p-form__info" for="contact">お問い合わせ内容</label>
            <p>{{ $form_value['contact'] }}</p>
            <input
              class="c-form__input c-from__input--signup @error('email') c-error__input @enderror"
              type="hidden"
              name="contact"
              value="{{ $form_value['contact'] }}"
            />
       
            <div class="u-wrapp">
              <button
                class="c-btn c-btn__auth"
                type="submit"
                name="action"
                >内容の修正
            </button>

            <div class="u-wrapp">
              <button
                class="c-btn c-btn__auth"
                type="submit"
                name="action"
                >送信する
            </button>
            </div>
          </form>
       
        </div>
      </div>
     
    </main>
@endsection
