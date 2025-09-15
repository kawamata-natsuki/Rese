<aside class="admin-sidebar">

  <!-- ロゴ -->
  <div class="header__logo">
    <a href="{{ route('admin.dashboard') }}" class="header__logo-link">
      <img src="/images/rese-logo.svg" alt="Rese logo" class="header__logo-img">
      <span class="header__logo-text">Rese</span>
    </a>
  </div>

  <!-- ナビゲーション -->
  <nav class="admin-sidebar__nav">
    <ul class="admin-sidebar__menu">

      <!-- Dashboard -->
      <li class="admin-sidebar__item">
        <a class="admin-sidebar__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
          href="{{ route('admin.dashboard') }}">
          <i class="fas fa-chart-line sidebar-icon"></i>
          <span class="admin-sidebar__text">Dashboard</span>
        </a>
      </li>

      <!-- Owners -->
      <li class="admin-sidebar__item">
        <details class="admin-accordion" {{ request()->routeIs('admin.shop-owners.*') ? 'open' : '' }}>
          <summary class="admin-sidebar__summary">
            <i class="fas fa-user-tie sidebar-icon"></i>
            <span class="admin-sidebar__text">Owners</span>
            <i class="fas fa-chevron-down chev" aria-hidden="true"></i>
          </summary>

          <ul class="admin-submenu">
            <li><a href="{{ route('admin.shop-owners.index') }}"
                class="{{ request()->routeIs('admin.shop-owners.index') ? 'is-current' : '' }} admin-submenu--inline">Owners List</a></li>
            <li><a href="{{ route('admin.shop-owners.create') }}"
                class="{{ request()->routeIs('admin.shop-owners.create') ? 'is-current' : '' }} admin-submenu--inline">Create New Owner</a></li>
          </ul>

        </details>
      </li>

      <!-- Shops：縦展開 -->
      <li class="admin-sidebar__item">
        <details class="admin-accordion" {{ request()->routeIs('admin.shops.*') ? 'open' : '' }}>
          <summary class="admin-sidebar__summary">
            <i class="fas fa-store"></i><span>Shops</span>
            <i class="fas fa-chevron-down chev" aria-hidden="true"></i>
          </summary>
          <ul class="admin-submenu admin-submenu--inline">
            <li><a href="{{ route('admin.shops.index') }}"
                class="{{ request()->routeIs('admin.shops.index') ? 'is-current' : '' }}">Shops List</a></li>
            <li><a href="{{ route('admin.shops.create') }}"
                class="{{ request()->routeIs('admin.shops.create') ? 'is-current' : '' }}">Create New Shop</a></li>
          </ul>
        </details>
      </li>
    </ul>

    <div class="admin-sidebar__footer">
      <ul class="admin-sidebar__menu">
        <li class="admin-sidebar__item">
          <a class="admin-sidebar__link {{ request()->routeIs('admin.account') ? 'is-active' : '' }}"
            href="{{ ('###') }}">
            <i class="fas fa-user"></i><span>Account</span>
          </a>
        </li>
      </ul>
      <form method="POST" action="{{ route('admin.logout') }}" class="admin-sidebar__logout">
        @csrf
        <button type="submit" class="admin-sidebar__logout-button">Log out</button>
      </form>
    </div>
  </nav>
</aside>