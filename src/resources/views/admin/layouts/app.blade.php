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

<body class="{{ request()->routeIs('admin.dashboard.index') ? 'onepage' : '' }}">
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
      <x-admin.topbar :unread-count="$unreadCount ?? 0" />
      @yield('content')
    </main>
  </div>

  <script>
    // CSRF を fetch で使う場合
    window.csrfToken = '{{ csrf_token() }}';
  </script>
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

  <script>
    // サイドバーのアコーディオンを単一オープンにする
    (function() {
      const sidebar = document.querySelector('.admin-sidebar');
      if (!sidebar) return;
      sidebar.addEventListener('toggle', function(e) {
        const target = e.target;
        if (!target || !(target instanceof HTMLDetailsElement)) return;
        if (!target.classList.contains('admin-accordion')) return;
        if (target.open) {
          sidebar.querySelectorAll('details.admin-accordion[open]').forEach(function(el) {
            if (el !== target) {
              el.removeAttribute('open');
            }
          });
        }
      }, true);
    })();
  </script>

  @stack('scripts')
  <script>
    // 管理者通知（簡易ポーリング + リスト描画）
    (function() {
      const bell = document.querySelector('.header__bell');
      const badge = document.querySelector('.header__bell-badge');
      const btnMarkAll = document.querySelector('.header__bell-markall');
      const list = document.getElementById('admin-bell-list');
      const empty = document.getElementById('admin-bell-empty');
      if (!bell) return;

      async function fetchUnread() {
        try {
          const res = await fetch('/admin/api/notifications/unread-count', {
            credentials: 'same-origin'
          });
          const json = await res.json();
          const n = json.unread ?? 0;
          if (n > 0) {
            bell.classList.add('has-unread');
            if (badge) {
              badge.textContent = n;
              badge.hidden = false;
            }
          } else {
            bell.classList.remove('has-unread');
            if (badge) {
              badge.textContent = '';
              badge.hidden = true;
            }
          }
        } catch (_) {}
      }

      if (btnMarkAll) {
        btnMarkAll.addEventListener('click', async () => {
          try {
            await fetch('/admin/api/notifications/mark-all-read', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': window.csrfToken
              },
              credentials: 'same-origin'
            });
            fetchUnread();
            fetchList();
          } catch (_) {}
        });
      }

      async function fetchList(page = 1) {
        try {
          const res = await fetch(`/admin/api/notifications?page=${page}`, {
            credentials: 'same-origin'
          });
          const json = await res.json();
          const items = Array.isArray(json.data) ? json.data : [];
          if (list) list.innerHTML = '';
          if (!items.length) {
            if (empty) empty.hidden = false;
            return;
          }
          if (empty) empty.hidden = true;
          items.forEach(n => {
            const li = document.createElement('li');
            li.className = `header__bell-item ${!n.read_at ? 'is-unread' : ''}`;
            const a = document.createElement('a');
            a.href = n.url || '#';
            a.innerHTML = `
              <div class="header__bell-item-title">${!n.read_at ? '<span class=\"dot\"></span>' : ''}${n.title || ''}</div>
              ${n.message ? `<div class=\"header__bell-item-msg\">${n.message}</div>` : ''}
              <div class="header__bell-item-time">${new Date(n.created_at || Date.now()).toLocaleString()}</div>
            `;
            li.appendChild(a);
            list?.appendChild(li);
          });
        } catch (_) {}
      }

      fetchUnread();
      fetchList();
      setInterval(fetchUnread, 30000); // 30秒毎

      // クリック外でドロップダウンを閉じる
      document.addEventListener('click', (e) => {
        if (!bell) return;
        const t = e.target;
        if (bell.hasAttribute('open') && t && !bell.contains(t)) {
          bell.removeAttribute('open');
        }
      });

      // Escキーで閉じる
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && bell && bell.hasAttribute('open')) {
          bell.removeAttribute('open');
        }
      });
    })();
  </script>
  @yield('js')
</body>

</html>