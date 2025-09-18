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







