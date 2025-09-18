@php($now = now()->timezone(config('app.timezone')))

<section class="admin-hero">
  <div class="admin-hero__row">
    {{-- カレンダーバッジ --}}
    <div class="calendar-badge" aria-label="{{ $now->format('Y-m-d') }}">
      <div class="calendar-badge__month">{{ strtoupper($now->format('M')) }}</div>
      <div class="calendar-badge__day">{{ $now->format('j') }}</div>
      <div class="calendar-badge__weekday">{{ strtoupper($now->format('D')) }}</div>
    </div>

    {{-- テキスト --}}
    <div class="admin-hero__content">
      <div class="admin-hero__text">
        <h1 class="admin-hero__title">Welcome back, Admin!</h1>
        <p class="admin-hero__message">Manage owners and shops, check reviews here</p>
      </div>

      {{-- カード2枚 --}}
      <div class="admin-hero__mini">
        {{-- Shops card --}}
        <article class="mini-card">
          <div class="mini-card__top"><i class="fas fa-store"></i><span>Shops</span></div>
          <div class="mini-card__bottom">
            <span class="mini-card__total">{{ number_format($totalShops ?? 0) }}</span>
            <span class="mini-card__delta">TOTAL</span>
          </div>
          <div class="mini-card__actions">
            <a href="{{ route('admin.shops.index') }}" class="btn btn--ghost">View</a>
            <a href="{{ route('admin.shops.create') }}" class="btn btn--primary">Create</a>
          </div>
        </article>

        {{-- Owners card --}}
        <article class="mini-card">
          <div class="mini-card__top"><i class="fas fa-user-tie"></i><span>Owners</span></div>
          <div class="mini-card__bottom">
            <span class="mini-card__total">{{ number_format($totalShopOwners ?? 0) }}</span>
            <span class="mini-card__delta">TOTAL</span>
          </div>
          <div class="mini-card__actions">
            <a href="{{ route('admin.shop-owners.index') }}" class="btn btn--ghost">View</a>
            <a href="{{ route('admin.shop-owners.create') }}" class="btn btn--primary">Create</a>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>