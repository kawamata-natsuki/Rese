@props([
    'unreadCount' => 0,
])

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