@extends('layouts.app')
@section('title', 'CryptoTrend | マイページ')
@section('description', 'CryptoTrendは、仮想通貨に関する情報を収集し、注目されている銘柄を最速でキャッチアップできるサービスです')
@section('keywords', '仮想通貨,仮想通貨ニュース,仮想通貨トレンド,検索,CryptoTrend,暗号通貨,Twitter,ツイッター')
@include('layouts.head')

@section('content')
<div id="app">
    <!-- app.bladeのyieldの箇所に読み込まれる -->
    <Mypage />
</div>
@endsection