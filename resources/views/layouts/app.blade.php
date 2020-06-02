<!doctype html>

<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/dist/css/style.css" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
  </head>
<body class="gray">
    <header class="l-header">
      <a class="p-header__logoLink" href="index.html">
        <img class="p-header__logo" src="storage/img/logo_trim.png" alt />
      </a>
      <div class="p-header--spmenu">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <nav class="p-header p-header__nav p-header__nav--sp">
        <ul class="p-header__list">
          <li class="p-header__item">
            <a class="p-header__item--link" href="login.html">ログイン</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" href="signup.html">新規登録</a>
          </li>
          <li class="p-header__item">
            <a class="p-header__item--link" href>このサービスについて</a>
          </li>
        </ul>
      </nav>
    </header>

    <main class="l-main">
            @yield('content')
    </main>

</body>
</html>
