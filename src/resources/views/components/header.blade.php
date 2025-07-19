<header class="header">
  <div class="header__inner">
    <!-- ハンバーガーメニュー -->
    <input type="checkbox" id="menu-toggle" class="header__menu-toggle" hidden>
    <label for="menu-toggle" class="header__hamburger">
      <span></span>
      <span></span>
      <span></span>
    </label>

    <!-- ロゴ -->
    <div class="header__logo">
      <a href="{{ route('shop.index') }}" class="header__logo-link">
        <span class="header__logo-text">Rese</span>
      </a>
    </div>

    <!-- フルスクリーンメニュー -->
    <nav class="header__nav-content">
      <ul class="header__nav-list">
        <li class="header__nav-item"><a href="#" class="header__nav-link">Home</a></li>
        <li class="header__nav-item"><a href="#" class="header__nav-link">Registration</a></li>
        <li class="header__nav-item"><a href="#" class="header__nav-link">Login</a></li>
      </ul>
    </nav>
  </div>

  @if (Request::is('/'))
  @include('components.search')
  @endif
</header>