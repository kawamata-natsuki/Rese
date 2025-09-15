/**
 * Admin Dashboard Charts Module
 * 管理者ダッシュボードのチャート機能を管理
 */
class AdminDashboard {
  constructor() {
    this.charts = {};
    this.config = {
      colors: {
        primary: '#2962ff',
        secondary: '#ff9800',
        areas: {
          '東京都': '#2962ff',
          '大阪府': '#ec407a', 
          '福岡県': '#ffb300'
        },
        areaNames: {
          '東京都': 'Tokyo',
          '大阪府': 'Osaka',
          '福岡県': 'Fukuoka'
        },
        fallback: '#90caf9'
      }
    };
    
    this.init();
  }

  /**
   * 初期化
   */
  init() {
    try {
      this.initLineChart();
      this.initPieChart();
      this.setupResize();
    } catch (error) {
      console.error('Dashboard initialization failed:', error);
    }
  }

  /**
   * 安全なオブジェクト取得
   */
  safeObject(obj, fallback = {}) {
    return (obj && typeof obj === 'object') ? obj : fallback;
  }

  /**
   * 安全な配列取得
   */
  safeArray(arr, fallback = []) {
    return Array.isArray(arr) ? arr : fallback;
  }

  /**
   * データセットからJSON解析
   */
  parseDataset(element, key) {
    try {
      const data = element.dataset[key] || '{}';
      return this.safeObject(JSON.parse(data));
    } catch (error) {
      console.warn(`Failed to parse dataset.${key}:`, error);
      return {};
    }
  }

  /**
   * 線形チャート初期化
   */
  initLineChart() {
    const element = document.getElementById('line-trend');
    if (!element) return;

    // 既存チャートの破棄
    this.destroyChart('line');

    const tsData = this.parseDataset(element, 'ts');
    const labels = this.safeArray(tsData.labels);
    const reservations = this.safeArray(tsData.reservations);
    const users = this.safeArray(tsData.users);

    const maxValue = Math.max(
      0, 
      ...(reservations.length ? reservations : [0]), 
      ...(users.length ? users : [0])
    );
    const suggestedMax = Math.max(8, Math.ceil(maxValue * 1.2));

    this.charts.line = new Chart(element.getContext('2d'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'Reservations',
            data: reservations,
            borderColor: this.config.colors.primary,
            backgroundColor: this.config.colors.primary,
            fill: false,
            tension: 0,
            pointRadius: 0,
            pointHoverRadius: 4
          },
          {
            label: 'Users',
            data: users,
            borderColor: this.config.colors.secondary,
            backgroundColor: this.config.colors.secondary,
            fill: false,
            tension: 0,
            pointRadius: 0,
            pointHoverRadius: 4
          }
        ]
      },
      options: this.getLineChartOptions(suggestedMax)
    });
  }

  /**
   * 線形チャートオプション取得
   */
  getLineChartOptions(suggestedMax) {
    return {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { 
        mode: 'index', 
        intersect: false 
      },
      scales: {
        x: {
          ticks: { 
            maxRotation: 0, 
            autoSkip: true, 
            maxTicksLimit: 8 
          },
          grid: { display: false }
        },
        y: {
          beginAtZero: true,
          suggestedMax,
          grid: { color: 'rgba(0,0,0,.06)' },
          ticks: { 
            callback: value => `${value}件` 
          }
        }
      },
      plugins: {
        legend: { 
          display: true, 
          position: 'bottom' 
        },
        tooltip: {
          callbacks: {
            label: (context) => 
              `${context.dataset.label}: ${context.parsed.y ?? 0} 件`
          }
        }
      }
    };
  }

  /**
   * 円グラフ初期化
   */
  initPieChart() {
    const element = document.getElementById('pie-area');
    if (!element) return;

    // 既存チャートの破棄
    this.destroyChart('pie');

    const pieData = this.parseDataset(element, 'pie');
    const labels = this.safeArray(pieData.labels);
    const data = this.safeArray(pieData.shops);

    const total = data.reduce((sum, value) => sum + (+value || 0), 0);
    const colors = labels.map(label => 
      this.config.colors.areas[label] || this.config.colors.fallback
    );

    this.charts.pie = new Chart(element.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          label: 'shops',
          data,
          backgroundColor: colors
        }]
      },
      options: this.getPieChartOptions(total),
      plugins: [this.createCenterTextPlugin(total)]
    });
  }

  /**
   * 円グラフオプション取得
   */
  getPieChartOptions(total) {
    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: {
            generateLabels: (chart) => this.generatePieLabels(chart, total)
          }
        },
        tooltip: {
          callbacks: {
            label: (context) => this.formatPieTooltip(context, total)
          }
        }
      }
    };
  }

  /**
   * 円グラフラベル生成
   */
  generatePieLabels(chart, total) {
    const { labels, datasets: [dataset] } = chart.data;
    const sum = total || 1;
    
    return labels.map((label, index) => {
      const value = +dataset.data[index] || 0;
      const percentage = Math.round((value / sum) * 100);
      const displayName = this.config.colors.areaNames[label] ?? label;
      
      return {
        text: `${displayName}  ${value}（${percentage}%）`,
        fillStyle: dataset.backgroundColor[index],
        strokeStyle: dataset.backgroundColor[index],
        lineWidth: 0
      };
    });
  }

  /**
   * 円グラフツールチップフォーマット
   */
  formatPieTooltip(context, total) {
    const value = +context.parsed || 0;
    const percentage = total ? Math.round((value / total) * 100) : 0;
    const displayName = this.config.colors.areaNames[context.label] ?? context.label;
    
    return `${displayName}: ${value} 件（${percentage}%）`;
  }

  /**
   * 中央テキストプラグイン作成
   */
  createCenterTextPlugin(total) {
    return {
      id: 'centerText',
      afterDatasetsDraw: (chart) => {
        const { ctx, chartArea } = chart;
        if (!chartArea) return;

        const centerX = (chartArea.left + chartArea.right) / 2;
        const centerY = (chartArea.top + chartArea.bottom) / 2;

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#111827';
        
        // 数値
        ctx.font = '700 22px system-ui, -apple-system, Segoe UI, Roboto';
        ctx.fillText(String(total), centerX, centerY - 10);
        
        // ラベル
        ctx.font = '600 12px system-ui, -apple-system, Segoe UI, Roboto';
        ctx.fillText('Shops', centerX, centerY + 12);
        
        ctx.restore();
      }
    };
  }

  /**
   * チャート破棄
   */
  destroyChart(name) {
    if (this.charts[name]) {
      this.charts[name].destroy();
      delete this.charts[name];
    }
  }

  /**
   * リサイズ処理設定
   */
  setupResize() {
    requestAnimationFrame(() => {
      Object.values(this.charts).forEach(chart => {
        if (chart && typeof chart.resize === 'function') {
          chart.resize();
        }
      });
    });
  }

  /**
   * クリーンアップ
   */
  destroy() {
    Object.keys(this.charts).forEach(name => {
      this.destroyChart(name);
    });
  }
}

// 初期化
(() => {
  let dashboard;

  function initDashboard() {
    try {
      dashboard = new AdminDashboard();
      
      // グローバルアクセス用（デバッグ等）
      window.__adminDashboard = dashboard;
    } catch (error) {
      console.error('Failed to initialize admin dashboard:', error);
    }
  }

  // DOM読み込み完了後に初期化
  if (document.readyState !== 'loading') {
    initDashboard();
  } else {
    document.addEventListener('DOMContentLoaded', initDashboard);
  }

  // ページ離脱時のクリーンアップ
  window.addEventListener('beforeunload', () => {
    if (dashboard) {
      dashboard.destroy();
    }
  });
})();
