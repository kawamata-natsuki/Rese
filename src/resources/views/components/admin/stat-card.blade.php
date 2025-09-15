@props([
'icon' => null, // 例: 'far fa-user'
'bg' => null, // 例: 'var(--pill-emerald)'
'fg' => null, // 例: 'var(--pill-emerald-fore)'
'label' => '', // 例: 'OWNERS'
'sublabel' => 'TOTAL',
'value' => 0,
])

<article class="admin-dashboard-page__stat-card">
  <div class="stat-card__icon" style="background:{{ $bg }}; color:{{ $fg }}">
    @if(Str::startsWith($icon, 'fa')) <i class="{{ $icon }}"></i> @else {{ $icon }} @endif
  </div>
  <div class="admin-dashboard-page__stat-label">
    <div class="admin-dashboard-page__stat-label-main">{{ $label }}</div>
    <div class="admin-dashboard-page__stat-sub">{{ $sublabel }}</div>
  </div>
  <p class="admin-dashboard-page__stat-count">{{ $value }}</p>
</article>