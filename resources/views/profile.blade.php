@extends('layouts.app')
@section('title', 'CryptoTrend | プロフィール')
@section('description', '仮想通貨トレンド検索サービス')
@section('keywords', '仮想通貨,CryptoTrend,検索,トレンド')
@include('layouts.head')

@section('content')
<div id="profire-component">
    <profile-component 
        endpoint="{{ url('profile') }}" 
        user="{{ $user->name }}"
        email="{{ $user->email }}"
        avatar="{{ $user->avatar }}"
    />
</div>
@endsection