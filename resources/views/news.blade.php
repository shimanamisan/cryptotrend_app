@extends('layouts.app')
@section('title', 'CryptoTrend | 仮想通貨ニュース一覧')
@section('description', '仮想通貨トレンド検索サービス')
@section('keywords', '仮想通貨,CryptoTrend,検索,トレンド')
@include('layouts.head')

@section('content')
    <!-- app.bladeのyieldの箇所に読み込まれる -->
    <news-component :news_data="{{ ($newsList) }}"/>
@endsection