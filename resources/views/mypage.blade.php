@extends('layouts.app')
@section('title', 'CryptoTrend | プロフィール')
@section('description', '仮想通貨トレンド検索サービス')
@section('keywords', '仮想通貨,CryptoTrend,検索,トレンド')
@include('layouts.head')

@section('content')
<div id="app">
    <!-- app.bladeのyieldの箇所に読み込まれる -->
    <Mypage />
</div>
@endsection