@props([
    'adminName' => 'Admin',
])

<section class="admin-hero">
    <div class="admin-hero__row">
        <div class="calendar-badge" aria-label="{{ now()->timezone(config('app.timezone'))->format('Y-m-d') }}">
            <div class="calendar-badge__month">{{ strtoupper(now()->timezone(config('app.timezone'))->format('M')) }}</div>
            <div class="calendar-badge__day">{{ now()->timezone(config('app.timezone'))->format('j') }}</div>
            <div class="calendar-badge__weekday">{{ strtoupper(now()->timezone(config('app.timezone'))->format('D')) }}</div>
        </div>

        <div class="admin-hero__content">
            <div class="admin-hero__image">
                <h1>Welcome back, {{ $adminName }}!</h1>
                <p>Manage owners and shops, check reviews here</p>
            </div>
            <div class="admin-hero__actions">
                <a href="{{ route('admin.shop-owners.create') }}" class="btn btn--primary">Create Owner</a>
                <a href="{{ route('admin.shops.create') }}" class="btn btn--ghost">Create Shop</a>
            </div>
        </div>
    </div>
</section>