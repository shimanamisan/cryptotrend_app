@extends('layouts.app')

@section('title', 'CryptoTrend | 仮想通貨トレンド')
@section('description', '仮想通貨トレンド検索サービス')
@section('keywords', '仮想通貨,CryptoTrend,検索,トレンド')
@include('layouts.head')

@section('content')
<main class="l-main">
      <section class="p-eyecatch">
        <div class="p-eyecatch__wrap">
          <h1 class="p-eyecatch__title">
            仮想通貨の最新情報は
            <br />CRYPTO TOREND
          </h1>
          <div class="p-eyecatch__content">
            <a href="{{ route('login') }}">
              <button class="c-btn c-btn__auth c-btn--radius u-btn">ログイン</button>
            </a>
            <div class="p-eyecatch__content--text">
              <p>既にアカウントをお持ちの方</p>
            </div>
          </div>
        </div>
      </section>
      <section class="c-container__index p-sitemap">
        <div class="p-sitemap__about">
          <h1 class="p-sitemap__title">CRYPTO TORENDとは？</h1>

          <hr class="u-line" />
          <div>
            <span class="p-sitemap__about--text">
              CRYPTO
              TORENDはTwitterを利用した仮想通貨のトレンドを検索するサービスです。
            </span>

            <span class="p-sitemap__about--text">注目されている銘柄を知ることにより効率よく投資先の銘柄を選択することができます。</span>
          </div>
        </div>
      </section>
      <section class="c-container__bg">
        <div class="c-container__index">
          <div class="p-sitemap">
            <div class="p-sitemap__pic">
              <img src="{{ asset('storage') }}/img/section01.png" alt />
            </div>
            <div class="p-sitemap__info">
              <h1 class="p-sitemap__title u-margin--bottom u-padding--top">SNSで話題の銘柄をウォッチ！</h1>
              <p class="p-sitemap__text">
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
              </p>
            </div>
          </div>
        </div>
      </section>
      <section class="c-container__bg">
        <div class="c-container__index">
          <div class="p-sitemap p-sitemap--reverse">
            <div class="p-sitemap__pic">
              <img src="{{ asset('storage') }}/img/section02.jpg" alt />
            </div>
            <div class="p-sitemap__info">
              <h1 class="p-sitemap__title u-margin--bottom u-padding--top">仮想通貨の最新ニュースをチェック！</h1>
              <p class="p-sitemap__text">
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
              </p>
            </div>
          </div>
        </div>
      </section>
      <section class="c-container__bg">
        <div class="c-container__index">
          <div class="p-sitemap">
            <div class="p-sitemap__pic">
              <img src="{{ asset('storage') }}/img/section03.jpg" alt />
            </div>
            <div class="p-sitemap__info">
              <h1 class="p-sitemap__title u-margin--bottom u-padding--top">気になるユーザーをフォロー！</h1>
              <p class="p-sitemap__text">
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
                サンプルテキスト。サンプルテキスト。サンプルテキスト。
              </p>
            </div>
          </div>
        </div>
      </section>
      <section class="p-eyecatch p-eyecatch__footer">
        <h1 class="p-eyecatch__footer--title">
          仮想通貨の最新情報は
          <br />CRYPTO TOREND
        </h1>
        <div class="p-eyecatch__footer--signup">
          <a href="{{ route('register') }}">
            <button class="c-btn c-btn__login c-btn--radius u-btn">新規登録</button>
          </a>
        </div>
      </section>
    </main>
    @include('layouts.footer')
@endsection
