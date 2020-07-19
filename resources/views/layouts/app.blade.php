<!DOCTYPE html>

<html lang="ja">

  <head>
    <!-- metaタグなどを別ファイルに切り出し -->
    @yield('head')
  </head>
  
<body class="gray">

    <header class="l-header">
      <a class="p-header__logoLink" href="{{ route('home') }}">
        <img class="p-header__logo" src="{{ asset('images/header_logo.png') }}" alt />
      </a>
      <div class="p-header--spmenu">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <nav class="p-header p-header__nav p-header__nav--sp">
        <ul class="p-header__list">
        @guest
          <li class="p-header__item">
            <a class="p-header__item--link" href="{{ route('login') }}">ログイン</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" href="{{ route('register') }}">新規登録</a>
          </li>
          @else
          <li class="p-header__item">
            <a class="p-header__item--link" href="{{ route('userList.index') }}">関連アカウント</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" href="{{ route('getNews.index') }}">仮想通貨ニュース</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" href="{{ route('conins.index') }}">仮想通貨トレンド</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" href="{{ route('mypage.index') }}">マイページ</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" 
            href="{{ route('logout') }}"
            onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();"
            >ログアウト</a>
            <form id="logout-form" method="post" action="{{ route('logout') }}" style="display:none;">
              @csrf
            </form>
          </li>
          @endguest
          <!-- <li class="p-header__item">
            <a class="p-header__item--link" href>このサービスについて</a>
          </li> -->
        </ul>
      </nav>
    </header>
    <div class="u-msg__withdraw js-flash-msg" style="display:none;">
    @if(Session::has('withdraw_message'))
            <p>{{ session('withdraw_message') }}</p>
    @endif
    </div>


  @yield('content')

</body>
</html>
