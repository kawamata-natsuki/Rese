<section class="admin-dashboard-page__line">
  <div class="admin-dashboard-page__graph-panel">
    <div class="admin-dashboard-page__graph-panel-header">
      <div class="admin-dashboard-page__latest-label">Reservations / Users / No-show Trend – Last 30 Days</div>
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