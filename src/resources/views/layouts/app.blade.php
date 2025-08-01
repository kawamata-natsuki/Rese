<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- フォント -->
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">

  <!-- components -->
  <link rel="stylesheet" href="{{ asset('css/components/header.css') }}">
  <link rel="stylesheet" href="{{ asset('css/components/search-form.css') }}">

  <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
  <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
  @yield('css')

  <title>
    @yield('title', 'Rese')
  </title>
</head>

<body>
  @if (!Request::routeIs('verification.notice'))
  @include('components.header')
  @endif

  <!-- フラッシュメッセージ -->
  @if (session('success') || session('error'))
  <div class="flash-message
    {{ session('success') ? 'flash-message--success' : 'flash-message--error' }}
    is-visible">
    {{ session('success') ?? session('error') }}
  </div>
  @endif

  <main>
    @yield('content')
  </main>

  <script>
    // フラッシュメッセージをフェードアウト
    setTimeout(() => {
      const flash = document.querySelector('.flash-message');
      if (flash) {
        flash.style.opacity = '0';
        setTimeout(() => flash.remove(), 500);
      }
    }, 3000);
  </script>

  @stack('scripts')
</body>

</html>