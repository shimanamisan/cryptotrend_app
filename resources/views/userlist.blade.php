@extends('layouts.app')

@section('title', 'CryptoTrend | 仮想通貨トレンド')
@section('description', '仮想通貨トレンド検索サービス')
@section('keywords', '仮想通貨,CryptoTrend,検索,トレンド')
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
    <h1 class="p-twuser__title">関連アカウント一覧</h1>
    <section class="c-container c-container__twusr">
        <h2 class="p-twuser__title p-twuser__gest">こちらのページはTwitterアカウントを登録することでご利用頂けます。</h2>
        <div class="u-wrapp">
         
        <a id="js-redirect" href="{{ route('userList.redirect') }}" 
        class="c-btn c-btn__twitter p-twuser__gest__btn"
        >Twitterアカウントを登録する</a>
   
     </div>
    </section>
</main>

@endif
@endsection