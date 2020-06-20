<!doctype html>

<html lang="ja">
  <head>
    <!-- metaタグなどを別ファイルに切り出し -->
    @yield('head')
  </head>
<body class="gray">
    <header class="l-header">
      <a class="p-header__logoLink" href="{{ route('home') }}">
        <img class="p-header__logo" src="{{ asset('storage') }}/img/logo_trim.png" alt />
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
            <a class="p-header__item--link" href="{{ route('userList.index') }}">ユーザー一覧</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" href="{{ route('getNews.index') }}">仮想通貨ニュース一覧</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" href="{{ route('searchTweet') }}">トレンド一覧</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" href="{{ route('profile.showProfileForm') }}">プロフィール編集</a>
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
          <li class="p-header__item">
            <a class="p-header__item--link" href>このサービスについて</a>
          </li>
        </ul>
      </nav>
    </header>
    @if(Session::has('message'))
    <div class="u-flashmsg">
      <p>{{ session('message') }}</p>
    </div>
    @endif

    
@yield('content')


</body>
</html>
