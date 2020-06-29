@extends('layouts.app')

@section('title', 'CryptoTrend | 仮想通貨トレンド')
@section('description', '仮想通貨トレンド検索サービス')
@section('keywords', '仮想通貨,CryptoTrend,検索,トレンド')
@include('layouts.head')

@section('content')
    <!-- app.bladeのyieldの箇所に読み込まれる -->
    <twitteruser-component
    :tw_user="{{ ($tw_user) }}"
    :user="{{ $user }}"
    />

@endsection