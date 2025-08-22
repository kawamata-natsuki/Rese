<header class="header">
  <div class="header__nav">
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

    <div class="header__right">
      @if (request()->routeIs('login.view', 'register.view', 'user.reservations.done'))
      <div class="header__search header__search--placeholder" aria-hidden="true"></div>
      @else
      @include('components.search-form')
      @endif

      @auth
      <div class="header__user">
        <span class="header__avatar"><i class="fas fa-user"></i></span>
        <span class="header__user-name">{{ Auth::user()->name }}</span>
      </div>
      @endauth
    </div>

    <!-- フルスクリーンメニュー -->
    <nav class="header__nav-content">
      <ul class="header__nav-list">

        @guest
        <li class="header__nav-item">
          <a href="{{route('register')}}" class="header__nav-link">
            Registration
          </a>
        </li>
        <li class="header__nav-item">
          <a href="{{route('login')}}" class="header__nav-link">
            Login
          </a>
        </li>
        @endguest

        @auth
        <li class="header__nav-item">
          <a href="{{route('shop.index')}}" class="header__nav-link">
            Home
          </a>
        </li>
        <li class="header__nav-item">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="header__nav-link">Logout</button>
          </form>
        </li>
        <li class="header__nav-item">
          <a href="{{route('user.mypage.index')}}" class="header__nav-link">
            Mypage
          </a>
        </li>
        @endauth
      </ul>
    </nav>
  </div>

</header>