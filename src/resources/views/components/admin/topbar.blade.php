@props(['unreadCount' => 0])

<div class="admin-topbar">
  <div class="admin-topbar__inner">

    {{-- 左：検索バー --}}
    <div class="admin-topbar__search">
      <form id="search-form" class="search-form" autocomplete="off">
        <div class="search-form__group search-form__group--input">
          <div class="search-form__input-wrapper">
            <span class="search-form__search-icon"><i class="fas fa-search"></i></span>
            <input type="text" name="keyword" id="admin-global-search" value="" placeholder="Search across admin..." class="search-form__input" aria-autocomplete="list" aria-controls="admin-search-panel">
          </div>
        </div>
        <div class="search-form__group search-form__group--reset">
          <button type="button" id="reset-button" class="search-form__reset-button">Reset</button>
        </div>
      </form>

      <div id="admin-search-panel" class="admin-search-panel" role="listbox" hidden>
        <div class="admin-search-panel__empty" hidden>No results</div>
        <div class="admin-search-panel__section" data-section="shops">
          <div class="admin-search-panel__title"><i class="fas fa-store"></i> Shops</div>
          <ul class="admin-search-panel__list"></ul>
        </div>
        <div class="admin-search-panel__section" data-section="owners">
          <div class="admin-search-panel__title"><i class="fas fa-user-tie"></i> Owners</div>
          <ul class="admin-search-panel__list"></ul>
        </div>
        <div class="admin-search-panel__section" data-section="users">
          <div class="admin-search-panel__title"><i class="fas fa-user"></i> Users</div>
          <ul class="admin-search-panel__list"></ul>
        </div>
        <div class="admin-search-panel__section" data-section="reservations">
          <div class="admin-search-panel__title"><i class="fas fa-calendar-check"></i> Reservations</div>
          <ul class="admin-search-panel__list"></ul>
        </div>
        <div class="admin-search-panel__section" data-section="reviews">
          <div class="admin-search-panel__title"><i class="fas fa-star"></i> Reviews</div>
          <ul class="admin-search-panel__list"></ul>
        </div>
      </div>
    </div>

    {{-- 右：通知ベル、ユーザーメニュー --}}
    <div class="admin-topbar__right">
      {{-- 通知ベル --}}
      <details class="header__bell {{ ($unreadCount ?? 0) > 0 ? 'has-unread' : '' }}">
        <summary class="header__bell-button" aria-label="通知">
          <i class="fas fa-bell"></i>
          <span class="header__bell-badge" {{ ($unreadCount ?? 0) > 0 ? '' : 'hidden' }}>{{ $unreadCount ?? 0 }}</span>
        </summary>
        <div class="header__bell-dropdown" role="menu">
          <div class="header__bell-head">
            <span class="header__bell-title">Notifications</span>
            <button type="button" class="header__bell-markall">Mark all as read</button>
          </div>
          <ul class="header__bell-list" id="admin-bell-list"></ul>
          <div class="header__bell-empty" id="admin-bell-empty" hidden>No notifications</div>
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