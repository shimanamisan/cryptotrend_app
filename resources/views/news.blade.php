@extends('layouts.app')
@section('title', 'CryptoTrend | 仮想通貨ニュース一覧')
@section('description', '仮想通貨トレンド検索サービス')
@include('layouts.head')

@section('content')
<div id="news-component">
    <news-component :news_data="{{ json_encode($list_gn) }}"/>
</div>
@endsection