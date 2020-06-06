@extends('layouts.app')
@section('title', 'CryptoTrend | プロフィール')
@section('description', '仮想通貨トレンド検索サービス')
@include('layouts.head')

@section('content')
<div id="profire-component">
    <profile-component 
    endpoint="{{ route('home') }}" 
    user="{{ $user->name }}"
    email="{{ $user->email }}"/>
</div>
@endsection