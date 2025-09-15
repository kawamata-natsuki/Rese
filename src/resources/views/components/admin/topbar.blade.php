@props(['unreadCount' => 0])

<div class="admin-topbar">
  <div class="admin-topbar__inner">

    {{-- 左：検索バー --}}
    <div class="admin-topbar__search">
      <form id="search-form" class="search-form">
        <div class="search-form__group search-form__group--input">
          <div class="search-form__input-wrapper">
            <span class="search-form__search-icon"><i class="fas fa-search"></i></span>
            <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}" placeholder="Search..." class="search-form__input">
          </div>
        </div>
        <div class="search-form__group search-form__group--reset">
          <button type="button" id="reset-button" class="search-form__reset-button">Reset</button>
        </div>
      </form>
    </div>

    {{-- 右：通知ベル、ユーザーメニュー --}}
    <div class="admin-topbar__right">
      {{-- 通知ベル --}}
      <details class="header__bell">
        <summary class="header__bell-button" aria-label="通知">
          <i class="fas fa-bell"></i>
          @if($unreadCount > 0)
          <span class="header__bell-badge">{{ $unreadCount }}</span>
          @endif
        </summary>
        <div class="header__bell-dropdown" role="menu">
          <div class="header__bell-head">
            <span class="header__bell-title">Notifications</span>
            <button type="button" class="header__bell-markall">Mark all as read</button>
          </div>
          <ul class="header__bell-list">
            <li class="header__bell-item is-unread">
              <a href="#">
                <div class="header__bell-item-title"><span class="dot"></span>新しいレビューが届きました</div>
                <div class="header__bell-item-msg">寿司 仙人 に5件の新規レビューがあります。</div>
                <div class="header__bell-item-time">2分前</div>
              </a>
            </li>
            <li class="header__bell-item">
              <a href="#">
                <div class="header__bell-item-title">オーナー招待が完了しました</div>
                <div class="header__bell-item-time">昨日</div>
              </a>
            </li>
          </ul>
        </div>
      </details>

      {{-- ユーザーメニュー --}}
      <details class="user-menu">
        <summary class="user-menu__summary">
          <span class="header__avatar"><i class="far fa-user-circle"></i></span>
          <span class="header__user-name">{{ optional(auth('admin')->user())->name ?? 'Admin' }}</span>
          <i class="fas fa-chevron-down user-menu__chev"></i>
        </summary>
        <div class="user-menu__dropdown">
          <ul class="user-menu__list" role="menu">
            <li role="none"><a role="menuitem" href="{{ route('user.mypage.index') }}">Mypage</a></li>
            <li role="none">
              <form role="none" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="user-menu__logout" role="menuitem">Logout</button>
              </form>
            </li>
          </ul>
        </div>
      </details>
    </div>
  </div>
</div>


