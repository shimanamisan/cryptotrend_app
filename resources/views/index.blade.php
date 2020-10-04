@extends('layouts.app')

@section('title', 'CryptoTrend | 仮想通貨トレンド')
@section('description', 'CryptoTrendは、仮想通貨に関する情報を収集し、注目されている銘柄を最速でキャッチアップできるサービスです')
@section('keywords', '仮想通貨,仮想通貨ニュース,仮想通貨トレンド,検索,CryptoTrend,暗号通貨,Twitter,ツイッター')

@include('layouts.head')
@section('content')
<main class="l-main">
      <section class="p-eyecatch l-main__section">
        <div class="p-eyecatch__wrap c-anime__fadein">
          <h1 class="p-eyecatch__title">
            仮想通貨の最新情報は
            <br />CRYPTO TOREND
          </h1>
          @guest
          <div class="p-eyecatch__content">
            <a href="{{ route('register') }}" class="p-eyecatch__content--link">
              <button class="c-btn c-btn__auth c-btn--radius u-btn">新規登録</button>
            </a>
            <div class="p-eyecatch__content--text">
            <a class="c-link__common" href="{{ route('login') }}"><p>既にアカウントをお持ちの方</p></a>
            </div>
          </div>
          @else
          <div class="p-eyecatch__content">
            <a href="{{ route('conins.index') }}" class="p-eyecatch__content--link">
              <button class="c-btn c-btn__auth c-btn--radius u-btn">トレンドを見る</button>
            </a>
            <div class="p-eyecatch__content--text">
            <a class="c-link__common" href="{{ route('userlist.index') }}"><p>関連アカウントをフォローする</p></a>
            </div>
          </div>
          @endguest
        </div>
      </section>
      <section class="c-container__index p-sitemap l-main__section">
        <div  class="p-sitemap__about js-scroll">
          <h1 class="p-sitemap__title">CRYPTO TRENDとは？</h1>

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
      <section class="c-container__bg l-main__section">
        <div class="c-container__index">
          <div class="p-sitemap js-scroll">
            <div class="p-sitemap__pic">
              <img src="{{ asset('images/top_figure_01.png') }}" alt />
            </div>
            <div class="p-sitemap__info">
              <h1 class="p-sitemap__title u-margin__bottom--G p-sitemap__pd__top--lg">SNSで話題の銘柄をウォッチ！</h1>
              <p class="p-sitemap__text">
                Twitterで今話題の銘柄は何なのか？
                タイムラインに投稿されている、各銘柄ごとのツイート数を集計し、
                トレンドの動向をチェックすることが出来ます。
              </p>
            </div>
          </div>
        </div>
      </section>
      <section class="c-container__bg l-main__section">
        <div class="c-container__index">
          <div class="p-sitemap p-sitemap--reverse js-scroll">
            <div class="p-sitemap__pic">
              <img src="{{ asset('images/top_figure_02.png') }}" alt />
            </div>
            <div class="p-sitemap__info">
              <h1 class="p-sitemap__title u-margin__bottom--G p-sitemap__pd__top--lg">最新ニュースをチェック！</h1>
              <p class="p-sitemap__text">
                仮想通貨関連のニュースのキーワードを元に、Googleニュースをまとめています。
                自分で検索する手間が省けるので、すぐに最新ニュースをチェックすることが出来ます。
              </p>
            </div>
          </div>
        </div>
      </section>
      <section class="c-container__bg l-main__section">
        <div class="c-container__index">
          <div class="p-sitemap js-scroll">
            <div class="p-sitemap__pic">
              <img src="{{ asset('images/top_figure_03.png') }}" alt />
            </div>
            <div class="p-sitemap__info">
              <h1 class="p-sitemap__title u-margin__bottom--G p-sitemap__pd__top--lg">気になるユーザーをフォロー！</h1>
              <p class="p-sitemap__text">
                仮想通貨関連の情報を発信しているユーザーをフォローすることが出来ます！
                自動フォロー機能を有効することによって、一覧表示されているユーザーを自動でフォローすることが出来ます。
                もちろん、ご自身で個別にフォローすることも可能です。
              </p>
            </div>
          </div>
        </div>
      </section>
      <section class="p-eyecatch p-eyecatch__footer l-main__section">
        <h1 class="p-eyecatch__footer--title">
          仮想通貨の最新情報は
          <br />CRYPTO TOREND
        </h1>
        @guest
        <div class="p-eyecatch__footer--signup">
          <a href="{{ route('register') }}">
            <button class="c-btn c-btn__auth c-btn--radius u-btn">新規登録</button>
          </a>
        </div>
        @else
        <div class="p-eyecatch__footer--signup">
          <a href="{{ route('getnews.index') }}">
            <button class="c-btn c-btn__auth c-btn--radius">仮想通貨ニュースを見る</button>
          </a>
        </div>
        @endguest
      </section>
    </main>
    @include('layouts.footer')
@endsection
