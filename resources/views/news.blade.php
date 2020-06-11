@extends('layouts.app')
@section('title', 'CryptoTrend | 仮想通貨ニュース一覧')
@section('description', '仮想通貨トレンド検索サービス')
@section('keywords', '仮想通貨,CryptoTrend,検索,トレンド')
@include('layouts.head')

@section('content')
<div id="news-component">
    <!-- <news-component :news_data="{{ json_encode($newsList) }}"/> -->
    <news-component :news_data="{{ ($newsList) }}"/>
</div>
@endsection