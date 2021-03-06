@extends('layouts.app')

@section('title', 'CryptoTrend | 仮想通貨トレンド')
@section('description', 'CryptoTrendは、仮想通貨に関する情報を収集し、注目されている銘柄を最速でキャッチアップできるサービスです')
@section('keywords', '仮想通貨,仮想通貨ニュース,仮想通貨トレンド,検索,CryptoTrend,暗号通貨,Twitter,ツイッター')
@include('layouts.head')

@section('content')

<!-- Twitter認証しているユーザーだったら、関連ユーザー一覧を表示させる -->
@if(Session::has('twitter_id'))
       
<div id="app">
    <!-- app.bladeのyieldの箇所に読み込まれる -->
    <User-List :follow_list="{{ ($follow_list) }}" :user="{{ $user }}"/>
</div>

@else

<main class="l-main l-main__common">
    <h1 class="c-title c-title__twuser">関連アカウント一覧</h1>
    <section class="c-container c-container__twusr">
        <h2 class="c-title__twuser__guest">こちらのページはTwitterアカウントを登録することでご利用頂けます。</h2>
        <div class="u-wrapp">
            <p class="u-margin__bottom--m">※1ユーザーにつき、Twitterアカウントを1つ登録できます。</p>
            @if(Session::has('error_message'))
            <div class="c-error__twuser__wrapp">
                <div class="c-error__authflash c-error__twuser u-margin__bottom--m">
                <p>{{ session('error_message') }}</p>
                </div>
            </div>
            @endif
            <a id="js-redirect" href="{{ route('twitter.register') }}" 
            class="c-btn c-btn__twitter p-twuser__gest__btn"
            >Twitterアカウントを登録する</a>
        </div>
    </section>
</main>

@endif

@endsection