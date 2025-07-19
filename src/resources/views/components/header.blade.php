<header class="header">
  <div class="header__inner"><!-- ハンバーガーメニュー -->
    <button class="hamburger-overlay" aria-label="メニュー" aria-controls="overlay-menu" aria-expanded="false">
      <span class="hamburger-overlay__line"></span>
      <span class="hamburger-overlay__line"></span>
      <span class="hamburger-overlay__line"></span>
    </button>

    <!-- ロゴ -->
    <div class="header__logo">
      <a href="{{ route('shop.index') }}" class="header__logo--link">
        <span class="logo-text">Rese</span>
      </a>
    </div>
  </div>

  <!-- フルスクリーンメニュー -->
  <nav id="overlay-menu" class="nav-overlay" aria-hidden="true">
    <div class="nav-overlay__content">
      <ul class="nav-overlay__list">
        <li class="nav-overlay__item">
          <a href="#" class="nav-overlay__link">Home</a>
        </li>
        <li class="nav-overlay__item">
          <a href="#" class="nav-overlay__link">Registration</a>
        </li>
        <li class="nav-overlay__item">
          <a href="#" class="nav-overlay__link">Login</a>
        </li>
      </ul>
    </div>
  </nav>

  @if (Request::is('/'))
  @include('components.search')
  @endif
</header>