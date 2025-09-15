@props([
    'users30d' => 0,
    'reservations30d' => 0,
    'reviews30d' => 0,
    'avgRating30d' => 0,
    'activeRate' => 0,
    'cancellationRate' => 0,
])

<section class="admin-dashboard-page__stats-section">
    <div class="admin-dashboard-page__stats-row">
        <div class="admin-dashboard-page__stats">
            <x-admin.stat-card 
                icon="fas fa-users" 
                bg="var(--pill-amber)" 
                fg="var(--pill-amber-fore)" 
                label="USERS" 
                sublabel="30D" 
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
                icon="fas fa-star-half-alt" 
                bg="var(--pill-violet)" 
                fg="var(--pill-violet-fore)" 
                label="AVG ★" 
                sublabel="30D" 
                :value="number_format($avgRating30d, 1)" />
            
            <x-admin.stat-card 
                icon="fas fa-fire" 
                bg="var(--pill-emerald)" 
                fg="var(--pill-emerald-fore)" 
                label="ACTIVE %" 
                sublabel="SHOPS ≥5•30D" 
                :value="number_format($activeRate, 1) . '%'" />
            
            <x-admin.stat-card 
                icon="fas fa-ban" 
                bg="var(--pill-pink)" 
                fg="var(--pill-pink-fore)" 
                label="CANCEL %" 
                sublabel="30D" 
                :value="number_format($cancellationRate, 1) . '%'" />
        </div>
    </div>
</section>