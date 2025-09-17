<section class="admin-dashboard-page__stats-section">
  <div class="admin-dashboard-page__stats-row">
    <div class="admin-dashboard-page__stats">
      @php
      $fmtPercent = function($v) {
      $v = (float)$v;
      return (fmod($v, 1.0) === 0.0)
      ? number_format($v, 0) . '%'
      : number_format($v, 1) . '%';
      };
      @endphp
      <x-admin.stat-card
        icon="fas fa-users"
        bg="var(--pill-amber)"
        fg="var(--pill-amber-fore)"
        label="USERS" sublabel="30D"
        :value="$users30d" />
      <x-admin.stat-card
        icon="fas fa-calendar-check"
        bg="var(--pill-pink)"
        fg="var(--pill-pink-fore)"
        label="RESERVATIONS"
        sublabel="30D"
        :value="$reservations30d" />
      <x-admin.stat-card
        icon="fas fa-star"
        bg="var(--pill-violet)"
        fg="var(--pill-violet-fore)"
        label="REVIEWS"
        sublabel="30D"
        :value="$reviews30d" />
      <x-admin.stat-card
        icon="fas fa-exclamation-triangle"
        bg="var(--pill-indigo)"
        fg="var(--pill-indigo-fore)"
        label="NO-SHOW %"
        sublabel="30D"
        :value="$fmtPercent($noShowRate)" />
      <x-admin.stat-card
        icon="fas fa-store"
        bg="var(--pill-sky)"
        fg="var(--pill-sky-fore)"
        label="SHOPS"
        sublabel="30D"
        :value="number_format($shops30d ?? 0)" />
      <x-admin.stat-card
        icon="fas fa-user-tie"
        bg="var(--pill-teal)"
        fg="var(--pill-teal-fore)"
        label="OWNERS"
        sublabel="30D"
        :value="number_format($owners30d ?? 0)" />
      <x-admin.stat-card
        icon="fas fa-fire"
        bg="var(--pill-emerald)"
        fg="var(--pill-emerald-fore)"
        label="ACTIVE %"
        sublabel="SHOPS ≥5•30D"
        :value="$fmtPercent($activeRate)" />
      <x-admin.stat-card
        icon="fas fa-ban"
        bg="var(--pill-lime)"
        fg="var(--pill-lime-fore)"
        label="CANCEL %"
        sublabel="30D"
        :value="$fmtPercent($cancellationRate)" />
    </div>
  </div>
</section>