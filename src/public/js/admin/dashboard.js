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
    const noShowRateMA7 = Array.isArray(ts.noShowRateMA7) ? ts.noShowRateMA7 : [];

    // 件数系（reservations/users）の最大を基準に軸スケールを決定
    const maxCount = Math.max(0,
      ...(reservations.length ? reservations : [0]),
      ...(users.length ? users : [0])
    );
    const suggestedMaxCount = Math.max(8, Math.ceil(maxCount * 1.2));
    const suggestedMaxPct = 100; // 率は常に0-100%

    window.__adminCharts.line = new Chart(el.getContext('2d'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'reservations',
            data: reservations,
            borderColor: '#60a5fa',
            backgroundColor: '#60a5fa',
            fill: false,
            tension: 0,
            pointRadius: 0,
            pointHoverRadius: 4,
            yAxisID: 'y'
          },
          {
            label: 'users',
            data: users,
            borderColor: '#f59e0b',
            backgroundColor: '#f59e0b',
            fill: false,
            tension: 0,
            pointRadius: 0,
            pointHoverRadius: 4,
            yAxisID: 'y'
          },
          {
            label: 'no-show % (7DMA)',
            data: noShowRateMA7,
            borderColor: '#ec407a',
            backgroundColor: '#ec407a',
            fill: false,
            borderWidth: 3,
            tension: 0,
            pointRadius: 0,
            pointHoverRadius: 0,
            yAxisID: 'y1'
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
            suggestedMax: suggestedMaxCount,
            grid: { color: 'rgba(0,0,0,.06)' },
            ticks: { callback: v => String(v) }
          },
          y1: {
            beginAtZero: true,
            position: 'right',
            suggestedMax: suggestedMaxPct,
            grid: { drawOnChartArea: false },
            ticks: {
              callback: v => `${v}%`
            }
          }
        },
        plugins: {
          legend: { display: true, position: 'bottom' },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const v = ctx.parsed.y ?? 0;
                if (ctx.dataset.label.includes('no-show %')) return `${ctx.dataset.label}: ${v}%`;
                return `${ctx.dataset.label}: ${v}`;
              }
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
    // Admin global search
    try {
      const input = document.getElementById('admin-global-search');
      const panel = document.getElementById('admin-search-panel');
      let aborter = null;
      const render = (data, q) => {
        if (!panel) return;
        const sections = ['shops', 'owners', 'users', 'reservations', 'reviews'];
        let total = 0;
        sections.forEach(key => {
          const section = panel.querySelector(`.admin-search-panel__section[data-section="${key}"]`);
          const ul = panel.querySelector(`.admin-search-panel__section[data-section="${key}"] .admin-search-panel__list`);
          if (!ul || !section) return;
          ul.innerHTML = '';
          const items = (data[key] || []);
          total += items.length;
          items.forEach(item => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = item.url || '#';
            a.innerHTML = `<i class="${item.icon || ''}"></i><span>${item.label}</span><small style="color:#6b7280">${item.sub || ''}</small>`;
            li.appendChild(a);
            ul.appendChild(li);
          });
          section.hidden = items.length === 0;
        });
        const empty = panel.querySelector('.admin-search-panel__empty');
        if (empty) {
          empty.textContent = q && q.length ? `No results for "${q}"` : 'No results';
          empty.hidden = total > 0;
        }
        panel.hidden = false;
      };
      const fetcher = async (q) => {
        if (!panel) return;
        if (aborter) aborter.abort();
        aborter = new AbortController();
        const res = await fetch(`/admin/search?q=${encodeURIComponent(q)}`, { signal: aborter.signal });
        const json = await res.json();
        render(json, q);
      };
      if (input && panel) {
        // 入力中はAJAX検索。フォーム送信は無効化
        const form = document.getElementById('search-form');
        if (form) {
          form.addEventListener('submit', (e) => e.preventDefault());
        }
        input.addEventListener('input', () => {
          const q = input.value.trim();
          if (q.length === 0) {
            panel.hidden = true;
            return;
          }
          fetcher(q).catch(() => { });
        });
        // Enterで先頭の検索結果に遷移
        input.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            const first = panel.querySelector('.admin-search-panel__section:not([hidden]) .admin-search-panel__list a');
            if (first) {
              window.location.href = first.getAttribute('href') || '#';
              panel.hidden = true;
            } else {
              // ヒットなしの明示表示
              const empty = panel.querySelector('.admin-search-panel__empty');
              if (empty) {
                const q = input.value.trim();
                empty.textContent = q.length ? `No results for "${q}"` : 'No results';
                empty.hidden = false;
              }
              panel.hidden = false;
            }
          }
        });
        document.addEventListener('click', (e) => {
          if (!panel.contains(e.target) && e.target !== input) {
            panel.hidden = true;
          }
        });
      }
    } catch (e) {
      // noop
    }
    requestAnimationFrame(() => {
      window.__adminCharts.line?.resize?.();
      window.__adminCharts.pie?.resize?.();
    });
  }

  if (document.readyState !== 'loading') boot();
  else document.addEventListener('DOMContentLoaded', boot);
})();

