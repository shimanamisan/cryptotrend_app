@extends('layouts.app')

@section('title', 'CryptoTrend | 仮想通貨トレンド')
@section('description', '仮想通貨トレンド検索サービス')
@section('keywords', '仮想通貨,CryptoTrend,検索,トレンド')
@include('layouts.head')

@section('content')

<main class="l-main l-main__common">
        <h1 class="p-news__title">Twitterユーザー一覧</h1>

        <section class="c-container c-container__news">
        @foreach ($user_list as $tweet)
                <div class="p-news__card">
                    <h5 class="d-inline mr-3"><strong>{{ $tweet->user_name }}</strong></h5>
                    <h5 class="d-inline mr-3"><strong>{{ $tweet->new_tweet }}</strong></h5>
                    <h5 class="d-inline mr-3"><strong>{{ $tweet->description }}</strong></h5>
                    <h6 class="d-inline text-secondary">{{ date('Y/m/d', strtotime($tweet->created_at)) }}</h6>
                    <p class="mt-3 mb-0">{{ $tweet->text }}</p>
                </div>
        @endforeach
        </section>
</main>
@endsection
        
        