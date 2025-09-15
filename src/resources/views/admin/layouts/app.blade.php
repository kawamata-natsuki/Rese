<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- CSS Reset & Fonts -->
  <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">

  <!-- Icon Font -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Flatpickr - Date Picker -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>

  <!-- Local CSS -->
  <!-- Components -->
  <link rel="stylesheet" href="{{ asset('css/components/search-form.css') }}">
  <link rel="stylesheet" href="{{ asset('css/components/header.css') }}">


  <!-- Layouts -->
  <link rel="stylesheet" href="{{ asset('css/admin/layouts/app.css') }}">
  <link rel="stylesheet" href="{{ asset('css/components/admin/sidebar.css') }}">
  @yield('css')

  <title>
    @yield('title', 'Rese')
  </title>
</head>

<body class="{{ request()->routeIs('admin.dashboard') ? 'onepage' : '' }}">
  <!-- フラッシュメッセージ -->
  @if (session('success') || session('error'))
  <div class="flash-message
    {{ session('success') ? 'flash-message--success' : 'flash-message--error' }}
    is-visible">
    {{ session('success') ?? session('error') }}
  </div>
  @endif

  <div class="admin-layout">
    <x-admin.sidebar />
    <main class="admin-layout__main">
      @yield('content')
    </main>
  </div>

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

  <script src="https://unpkg.com/alpinejs" defer></script>
  <script>
    document.querySelectorAll('.admin-sidebar__section--flyout .admin-sidebar__toggle')
      .forEach(trigger => {
        const parent = trigger.closest('.admin-sidebar__section--flyout');
        const submenu = parent.querySelector('.admin-sidebar__submenu');
        if (!submenu) return;

        // hoverで表示する運用でも、キーボード操作時のariaは更新しておく
        trigger.addEventListener('focus', () => trigger.setAttribute('aria-expanded', 'true'));
        trigger.addEventListener('blur', () => trigger.setAttribute('aria-expanded', 'false'));

        // サブメニュー内でフォーカスを持つ間は開いたまま扱い
        submenu.addEventListener('focusin', () => trigger.setAttribute('aria-expanded', 'true'));
        submenu.addEventListener('focusout', (e) => {
          if (!submenu.contains(e.relatedTarget)) {
            trigger.setAttribute('aria-expanded', 'false');
          }
        });
      });
  </script>

  @stack('scripts')
  @yield('js')
</body>

</html>