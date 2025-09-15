/* public/js/admin/dashboard.js */
(() => {
  // 多重初期化防止
  window.__adminCharts = window.__adminCharts || {};

  function safeObj(o, fallback = {}) {
    if (!o || typeof o !== 'object') return fallback;
    return o;
  }

  function initLineFromEl(el) {
    if (!el) return;
    window.__adminCharts.line?.destroy();

    let ts = {};
    try { ts = safeObj(JSON.parse(el.dataset.ts || '{}')); } catch (e) { ts = {}; }

    const labels = Array.isArray(ts.labels) ? ts.labels : [];
    const reservations = Array.isArray(ts.reservations) ? ts.reservations : [];
    const users = Array.isArray(ts.users) ? ts.users : [];

    const maxVal = Math.max(0, ...(reservations.length ? reservations : [0]), ...(users.length ? users : [0]));
    const suggestedMax = Math.max(8, Math.ceil(maxVal * 1.2));

    window.__adminCharts.line = new Chart(el.getContext('2d'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'reservations',
            data: reservations,
            borderColor: '#2962ff',
            backgroundColor: '#2962ff',
            fill: false,
            tension: 0,
            pointRadius: 0,
            pointHoverRadius: 4
          },
          {
            label: 'users',
            data: users,
            borderColor: '#ff9800',
            backgroundColor: '#ff9800',
            fill: false,
            tension: 0,
            pointRadius: 0,
            pointHoverRadius: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
          x: {
            ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
            grid: { display: false }
          },
          y: {
            beginAtZero: true,
            suggestedMax,
            grid: { color: 'rgba(0,0,0,.06)' },
            ticks: { callback: v => `${v}件` }
          }
        },
        plugins: {
          legend: { display: true, position: 'bottom' },
          tooltip: {
            callbacks: {
              label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y ?? 0} 件`
            }
          }
        }
      }
    });
  }

  function initPieFromEl(el) {
    if (!el) return;
    window.__adminCharts.pie?.destroy();

    let pie = {};
    try { pie = safeObj(JSON.parse(el.dataset.pie || '{}')); } catch (e) { pie = {}; }

    const labels = Array.isArray(pie.labels) ? pie.labels : [];
    const data = Array.isArray(pie.shops) ? pie.shops : [];

    const palette = { '東京都': '#2962ff', '大阪府': '#ec407a', '福岡県': '#ffb300' };
    const displayNames = { '東京都': 'Tokyo', '大阪府': 'Osaka', '福岡県': 'Fukuoka' };

    const total = data.reduce((a, b) => a + (+b || 0), 0);
    const colors = labels.map(l => palette[l] || '#90caf9');

    const centerText = {
      id: 'centerText',
      afterDatasetsDraw(chart) {
        const { ctx, chartArea } = chart;
        if (!chartArea) return;
        const x = (chartArea.left + chartArea.right) / 2;
        const y = (chartArea.top + chartArea.bottom) / 2;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#111827';
        ctx.font = '700 22px system-ui, -apple-system, Segoe UI, Roboto';
        ctx.fillText(String(total), x, y - 10);
        ctx.font = '600 12px system-ui, -apple-system, Segoe UI, Roboto';
        ctx.fillText('Shops', x, y + 12);
        ctx.restore();
      }
    };

    window.__adminCharts.pie = new Chart(el.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{ label: 'shops', data, backgroundColor: colors }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              generateLabels(chart) {
                const { labels: l, datasets: [ds] } = chart.data;
                const sum = (ds.data || []).reduce((a, b) => a + (+b || 0), 0) || 1;
                return l.map((label, i) => {
                  const v = +ds.data[i] || 0;
                  const pct = Math.round((v / sum) * 100);
                  return {
                    text: `${displayNames[label] ?? label}  ${v}（${pct}%）`,
                    fillStyle: ds.backgroundColor[i],
                    strokeStyle: ds.backgroundColor[i],
                    lineWidth: 0
                  };
                });
              }
            }
          },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const v = +ctx.parsed || 0;
                const pct = total ? Math.round((v / total) * 100) : 0;
                const name = displayNames[ctx.label] ?? ctx.label;
                return `${name}: ${v} 件（${pct}%）`;
              }
            }
          }
        }
      },
      plugins: [centerText]
    });
  }

  function boot() {
    initLineFromEl(document.getElementById('line-trend'));
    initPieFromEl(document.getElementById('pie-area'));
    requestAnimationFrame(() => {
      window.__adminCharts.line?.resize?.();
      window.__adminCharts.pie?.resize?.();
    });
  }

  if (document.readyState !== 'loading') boot();
  else document.addEventListener('DOMContentLoaded', boot);
})();
