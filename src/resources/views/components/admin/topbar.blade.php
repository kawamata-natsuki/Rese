@props([
    'adminName' => 'Admin',
    'unreadCount' => 0,
])

<div class="admin-topbar">
    <div class="admin-topbar__inner">
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

        <div class="admin-topbar__right">
            {{-- 通知ベル --}}
            <x-notification-bell :unreadCount="$unreadCount" />

            {{-- ユーザーメニュー --}}
            <details class="user-menu">
                <summary class="user-menu__summary">
                    <span class="header__avatar"><i class="far fa-user-circle"></i></span>
                    <span class="header__user-name">{{ $adminName }}</span>
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