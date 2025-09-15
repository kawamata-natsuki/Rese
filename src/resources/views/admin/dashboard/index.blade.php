@extends('admin.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/components/admin/stat-card.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/dashboard/index.css') }}">
@endsection

@section('title', '管理者画面ダッシュボード')

@section('content')
@php
$admin = auth('admin')->user();
$unreadCount = $unreadCount ?? 0;

$inactiveShops30d = $inactiveShops30d ?? ($inactiveCount30d ?? 0);
$topShops30d = isset($topShops30d) ? $topShops30d : collect();
@endphp

<div class="admin-dashboard-page">

  {{-- ===== Topbar ===== --}}
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
            <span class="header__user-name">{{ $admin->name ?? 'Admin' }}</span>
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

  {{-- ===== Body ===== --}}
  <div class="admin-dashboard-page__container">

    {{-- Hero --}}
    <section class="admin-hero">
      <div class="admin-hero__row">
        <div class="calendar-badge" aria-label="{{ now()->timezone(config('app.timezone'))->format('Y-m-d') }}">
          <div class="calendar-badge__month">{{ strtoupper(now()->timezone(config('app.timezone'))->format('M')) }}</div>
          <div class="calendar-badge__day">{{ now()->timezone(config('app.timezone'))->format('j') }}</div>
          <div class="calendar-badge__weekday">{{ strtoupper(now()->timezone(config('app.timezone'))->format('D')) }}</div>
        </div>

        <div class="admin-hero__content">
          <div class="admin-hero__image">
            <h1>Welcome back, Admin!!</h1>
            <p>Manage owners and shops, check reviews here</p>
          </div>
          <div class="admin-hero__actions">
            <a href="{{ route('admin.shop-owners.create') }}" class="btn btn--primary">Create Owner</a>
            <a href="{{ route('admin.shops.create') }}" class="btn btn--ghost">Create Shop</a>
          </div>
        </div>
      </div>
    </section>

    {{-- KPI 6枚 --}}
    <section class="admin-dashboard-page__stats-section">
      <div class="admin-dashboard-page__stats-row">
        <div class="admin-dashboard-page__stats">
          <x-admin.stat-card icon="fas fa-users" bg="var(--pill-amber)" fg="var(--pill-amber-fore)" label="USERS" sublabel="30D" :value="$users30d" />
          <x-admin.stat-card icon="fas fa-calendar-check" bg="var(--pill-pink)" fg="var(--pill-pink-fore)" label="RESERVATIONS" sublabel="30D" :value="$reservations30d" />
          <x-admin.stat-card icon="fas fa-star" bg="var(--pill-violet)" fg="var(--pill-violet-fore)" label="REVIEWS" sublabel="30D" :value="$reviews30d" />
          <x-admin.stat-card icon="fas fa-star-half-alt" bg="var(--pill-violet)" fg="var(--pill-violet-fore)" label="AVG ★" sublabel="30D" :value="number_format($avgRating30d, 1)" />
          <x-admin.stat-card icon="fas fa-fire" bg="var(--pill-emerald)" fg="var(--pill-emerald-fore)" label="ACTIVE %" sublabel="SHOPS ≥5•30D" :value="number_format($activeRate, 1) . '%'" />
          <x-admin.stat-card icon="fas fa-ban" bg="var(--pill-pink)" fg="var(--pill-pink-fore)" label="CANCEL %" sublabel="30D" :value="number_format($cancellationRate, 1) . '%'" />
        </div>
      </div>
    </section>

    {{-- 折れ線 --}}
    <section class="admin-dashboard-page__line">
      <div class="admin-dashboard-page__graph-panel">
        <div class="admin-dashboard-page__graph-panel-header">
          <div class="admin-dashboard-page__latest-label">Reservations, Users & Reviews – Last 30 Days</div>
        </div>
        <div class="chart-box">
          <canvas
            id="line-trend"
            aria-label="予約とユーザーの30日推移"
            role="img"
            data-ts='@json($charts["timeseries"] ?? (object)[])'>
          </canvas>
        </div>
      </div>
    </section>

    {{-- 円グラフ --}}
    <section class="admin-dashboard-page__pie">
      <div class="admin-dashboard-page__graph-panel">
        <div class="admin-dashboard-page__graph-panel-header">
          <div class="admin-dashboard-page__latest-label">Shops by Area</div>
        </div>
        <div class="chart-box">
          <canvas
            id="pie-area"
            aria-label="Shops by Area"
            role="img"
            data-pie='@json($charts["pie"] ?? (object)[])'>
          </canvas>
        </div>
      </div>
    </section>

    {{-- 右：Top / Inactive （上下2分割） --}}
    <section class="latest-panels">
      {{-- Top Shops (30D) --}}
      <article class="panel">
        <header class="panel__header">
          <h3>Top Shops (30D)</h3>
          <a href="{{ route('admin.shops.index', ['sort' => 'res30d']) }}" class="panel__link">All</a>
        </header>
        <ul class="mini-list">
          @forelse($topShops30d as $s)
          @php
          $total = (int)($s->total ?? 0);
          $cancelled = (int)($s->cancelled ?? 0);
          $rate = $total ? round(($cancelled / $total) * 100) : 0;
          @endphp
          <li>
            <span class="name" title="{{ $s->name }}">{{ $s->name }}</span>
            <span class="count">{{ $total }}</span>
            <span class="muted">{{ $rate }}%</span>
          </li>
          @empty
          <li><span class="muted">No data</span></li>
          @endforelse
        </ul>
      </article>

      {{-- Inactive Shops (30D) --}}
      <article class="panel">
        <header class="panel__header">
          <h3>Inactive Shops (30D)</h3>
          <a href="{{ route('admin.shops.index', ['inactive' => '30d']) }}" class="panel__link">Open list</a>
        </header>
        <ul class="panel__list">
          <li class="panel__item">
            <div class="avatar"><i class="far fa-eye-slash"></i></div>
            <div class="meta">
              <div class="meta__title" style="font-size:1.4rem;font-weight:800;">{{ $inactiveShops30d }}</div>
              <div class="meta__sub">No reservations in the last 30 days</div>
            </div>
          </li>
        </ul>
      </article>
    </section>

  </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="{{ asset('js/admin/dashboard.js') }}"></script>
@endsection