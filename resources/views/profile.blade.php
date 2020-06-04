@extends('layouts.app')
@section('title', 'CryptoTrend | プロフィール')
@section('description', '仮想通貨トレンド検索サービス')
@include('layouts.head')

@section('content')
<div id="profire-component">
    <profile-component 
    endpoint="{{ route('home') }}" />
</div>
@endsection