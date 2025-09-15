@props([
    'topShops30d' => collect(),
    'inactiveShops30d' => 0,
])

<section class="latest-panels">
    {{-- Top Shops (30D) --}}
    <article class="panel">
        <header class="panel__header">
            <h3>Top Shops (30D)</h3>
            <a href="{{ route('admin.shops.index', ['sort' => 'res30d']) }}" class="panel__link">All</a>
        </header>
        <ul class="mini-list">
            @forelse($topShops30d as $shop)
                @php
                    $total = (int)($shop->total ?? 0);
                    $cancelled = (int)($shop->cancelled ?? 0);
                    $rate = $total ? round(($cancelled / $total) * 100) : 0;
                @endphp
                <li>
                    <span class="name" title="{{ $shop->name }}">{{ $shop->name }}</span>
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