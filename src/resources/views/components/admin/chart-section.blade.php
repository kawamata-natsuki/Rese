@props([
    'charts' => [],
    'type' => 'line', // 'line' or 'pie'
    'title' => '',
    'canvasId' => '',
])

<section class="admin-dashboard-page__{{ $type }}">
    <div class="admin-dashboard-page__graph-panel">
        <div class="admin-dashboard-page__graph-panel-header">
            <div class="admin-dashboard-page__latest-label">{{ $title }}</div>
        </div>
        <div class="chart-box">
            <canvas
                id="{{ $canvasId }}"
                aria-label="{{ $title }}"
                role="img"
                @if($type === 'line')
                    data-ts='@json($charts["timeseries"] ?? (object)[])'
                @else
                    data-pie='@json($charts["pie"] ?? (object)[])'
                @endif>
            </canvas>
        </div>
    </div>
</section>