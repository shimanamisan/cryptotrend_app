@extends('layouts.app')
@section('title', 'CryptoTrend | Error')
@section('description', 'CryptoTrendは、仮想通貨に関する情報を収集し、注目されている銘柄を最速でキャッチアップできるサービスです')
@section('keywords', '仮想通貨,仮想通貨ニュース,仮想通貨トレンド,検索,CryptoTrend,暗号通貨,Twitter,ツイッター')
@include('layouts.head')

@section('content')

@php
$status_code = $exception->getStatusCode();
$message = $exception->getMessage();

if (! $message) {
    switch ($status_code) {
        case 400:
            $message = 'Bad Request';
            break;
        case 401:
            $message = '認証に失敗しました';
            break;
        case 403:
            $message = 'アクセス権がありません';
            break;
        case 404:
            $message = '存在しないページです';
            break;
        case 408:
            $message = 'タイムアウトです';
            break;
        case 414:
            $message = 'リクエストURIが長すぎます';
            break;
        case 419:
            $message = '不正なリクエストです';
            break;
        case 500:
            $message = '内部サーバーエラーが発生しました。しばらくお待ち下さい';
            break;
        case 503:
            $message = 'Service Unavailable';
            break;
        default:
            $message = 'エラー';
            break;
    }
}
@endphp

<div class="l-main l-main__common">
<h1 class="c-container c-container__index c-container__exception">{{ $status_code }} <br><p>｜</p> {{ $message }}</h1>
</div>

@endsection