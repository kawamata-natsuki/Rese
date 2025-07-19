<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
  <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
  @yield('css')

  <title>
    @yield('title', 'Rese')
  </title>
</head>

<body>
  @include('components.header')

  <main>
    @yield('content')
  </main>

  <!-- js -->
  <script src="{{ asset('js/hamburger-overlay') }}"></script>

</body>

</html>