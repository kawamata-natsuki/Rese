<header class="header">
  <div class="header__nav">
    <!-- ロゴ -->
    <div class="header__logo">
      <a href="{{ route('shop.index') }}" class="header__logo-link">
        <img src="/images/rese-logo.svg" alt="Rese logo" class="header__logo-img">
        <span class="header__logo-text">Rese</span>
      </a>
    </div>

    <div class="header__right">
      @if (request()->routeIs('login.view', 'register.view', 'user.reservations.done'))
      <div class="header__search header__search--placeholder" aria-hidden="true"></div>
      @else
      @include('components.search-form')
      @endif

      @auth
      @if (!request()->routeIs('login.view', 'register.view'))
      @include('components.notification-bell')

      <!-- User menu (details/summaryでJSレス) -->
      <details class="user-menu">
        <summary class="user-menu__summary" aria-label="User menu">
          <span class="header__avatar"><i class="fas fa-user"></i></span>
          <span class="header__user-name">{{ Auth::user()->name }}</span>
          <i class="fas fa-chevron-down user-menu__chev" aria-hidden="true"></i>
        </summary>

        <div class="user-menu__dropdown">
          <ul class="user-menu__list" role="menu">
            <li role="none">
              <a role="menuitem" href="{{ route('user.mypage.index') }}">Mypage</a>
            </li>
            <li role="none">
              <form role="none" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="user-menu__logout" role="menuitem">Logout</button>
              </form>
            </li>
          </ul>
        </div>
      </details>
      @endif
      @endauth
    </div>
  </div>

  <!-- details.user-menu のすぐ後などで読み込み -->
  <script>
    const menus = document.querySelectorAll('.user-menu');

    menus.forEach(menu => {
      const dropdown = menu.querySelector('.user-menu__dropdown');
      const items = menu.querySelectorAll('.user-menu__list a, .user-menu__list button');

      // 外クリック
      const onDocClick = (e) => {
        if (!menu.contains(e.target)) menu.removeAttribute('open');
      };
      document.addEventListener('click', onDocClick);

      // dropdownから出たら閉じる（少し猶予）
      let closeTimer = null;
      dropdown.addEventListener('mouseleave', () => {
        closeTimer = setTimeout(() => menu.removeAttribute('open'), 140);
      });
      dropdown.addEventListener('mouseenter', () => {
        if (closeTimer) clearTimeout(closeTimer);
      });

      // Esc
      menu.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') menu.removeAttribute('open');
      });

      // クリック後は閉じる
      items.forEach(el => el.addEventListener('click', () => menu.removeAttribute('open')));

      // フォーカスが外れたら閉じる
      menu.addEventListener('focusout', (e) => {
        if (!menu.contains(e.relatedTarget)) menu.removeAttribute('open');
      });

      // aria-expanded 同期（アクセシビリティ）
      const summary = menu.querySelector('.user-menu__summary');
      const sync = () => summary.setAttribute('aria-expanded', menu.hasAttribute('open') ? 'true' : 'false');
      summary.setAttribute('aria-haspopup', 'menu');
      menu.addEventListener('toggle', sync);
      sync();
    });
  </script>

</header>