<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
</body>

</html>