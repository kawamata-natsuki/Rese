<?php

namespace App\Services\Admin;

use App\Models\Area;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopOwner;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatisticsRepository
{
    /**
     * 期間内のユーザー数を取得
     */
    public function getUsersCount(array $dateRange): int
    {
        return User::whereBetween('created_at', [
            $dateRange['startUtc'],
            $dateRange['endUtc']
        ])->count();
    }

    /**
     * 期間内の予約数を取得
     */
    public function getReservationsCount(array $dateRange): int
    {
        return Reservation::whereBetween('created_at', [
            $dateRange['startUtc'],
            $dateRange['endUtc']
        ])->count();
    }

    /**
     * 期間内のレビュー数を取得
     */
    public function getReviewsCount(array $dateRange): int
    {
        return Review::whereBetween('created_at', [
            $dateRange['startUtc'],
            $dateRange['endUtc']
        ])->whereNull('deleted_at')->count();
    }

    /**
     * キャンセル率を取得
     */
    public function getCancellationRate(array $dateRange): float
    {
        $totalReservations = $this->getReservationsCount($dateRange);
        
        if ($totalReservations === 0) {
            return 0.0;
        }

        $cancelledReservations = Reservation::whereBetween('created_at', [
            $dateRange['startUtc'],
            $dateRange['endUtc']
        ])->where('reservation_status', 'cancelled')->count();

        return round(($cancelledReservations / $totalReservations) * 100, 1);
    }

    /**
     * 平均評価を取得
     */
    public function getAverageRating(array $dateRange): float
    {
        $avgRating = Review::whereBetween('created_at', [
            $dateRange['startUtc'],
            $dateRange['endUtc']
        ])
        ->whereNull('deleted_at')
        ->avg('rating');

        $rating = (float)($avgRating ?? 0.0);
        return max(0, min(5, $rating));
    }

    /**
     * アクティブ店舗データを取得
     */
    public function getActiveShopsData(array $dateRange, int $threshold = 5): array
    {
        $totalShops = Shop::count();

        $activeShopIds = Reservation::query()
            ->whereBetween('reservation_date', [
                $dateRange['startUtc'],
                $dateRange['endUtc']
            ])
            ->where('reservation_status', '!=', 'cancelled')
            ->groupBy('shop_id')
            ->havingRaw('COUNT(*) >= ?', [$threshold])
            ->pluck('shop_id');

        $activeShops = Shop::whereIn('id', $activeShopIds)->count();
        $activeRate = $totalShops > 0 ? round($activeShops / $totalShops * 100, 1) : 0.0;
        $dormantShops = max($totalShops - $activeShops, 0);

        return [
            'totalShops' => $totalShops,
            'activeShops' => $activeShops,
            'activeRate' => $activeRate,
            'dormantShops' => $dormantShops,
            'threshold' => $threshold,
        ];
    }

    /**
     * トップ店舗（予約数順）を取得
     */
    public function getTopShops(array $dateRange, int $limit = 5): \Illuminate\Support\Collection
    {
        return DB::table('shops')
            ->leftJoin('reservations', function ($join) use ($dateRange) {
                $join->on('reservations.shop_id', '=', 'shops.id')
                    ->whereBetween('reservations.created_at', [
                        $dateRange['startUtc'],
                        $dateRange['endUtc']
                    ])
                    ->whereNull('reservations.deleted_at');
            })
            ->whereNull('shops.deleted_at')
            ->groupBy('shops.id', 'shops.name')
            ->select(
                'shops.id',
                'shops.name',
                DB::raw('COUNT(reservations.id) as total'),
                DB::raw("SUM(CASE WHEN reservations.reservation_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            )
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->cancel_rate = $row->total > 0 
                    ? round(($row->cancelled / $row->total) * 100, 1) 
                    : 0.0;
                return $row;
            });
    }

    /**
     * 非アクティブ店舗数を取得
     */
    public function getInactiveShopsCount(array $dateRange): int
    {
        return DB::table('shops')
            ->whereNull('shops.deleted_at')
            ->whereNotExists(function ($query) use ($dateRange) {
                $query->select(DB::raw(1))
                    ->from('reservations')
                    ->whereColumn('reservations.shop_id', 'shops.id')
                    ->whereBetween('reservations.created_at', [
                        $dateRange['startUtc'],
                        $dateRange['endUtc']
                    ])
                    ->whereNull('reservations.deleted_at');
            })
            ->count();
    }

    /**
     * 日別の予約数を取得
     */
    public function getReservationsByDay(array $dateRange): array
    {
        return Reservation::selectRaw('reservation_date as d, COUNT(*) as c')
            ->whereBetween('reservation_date', [
                $dateRange['start']->toDateString(),
                $dateRange['end']->toDateString()
            ])
            ->groupBy('reservation_date')
            ->orderBy('reservation_date')
            ->pluck('c', 'd')
            ->all();
    }

    /**
     * 日別のレビュー数を取得
     */
    public function getReviewsByDay(array $dateRange): array
    {
        return Review::selectRaw("DATE(created_at) as d, COUNT(*) as c")
            ->whereBetween('created_at', [
                $dateRange['startUtc'],
                $dateRange['endUtc']
            ])
            ->whereNull('deleted_at')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->all();
    }

    /**
     * 日別のユーザー数を取得
     */
    public function getUsersByDay(array $dateRange): array
    {
        return User::selectRaw("DATE(created_at) as d, COUNT(*) as c")
            ->whereBetween('created_at', [
                $dateRange['startUtc'],
                $dateRange['endUtc']
            ])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->all();
    }

    /**
     * エリア別店舗数を取得
     */
    public function getShopsByArea(): \Illuminate\Database\Eloquent\Collection
    {
        return Area::withCount('shops')->get();
    }

    /**
     * エリア別予約数を取得
     */
    public function getReservationsByArea(array $dateRange): \Illuminate\Support\Collection
    {
        return DB::table('areas')
            ->selectRaw('areas.name as area, COUNT(reservations.id) as cnt')
            ->leftJoin('shops', 'shops.area_id', '=', 'areas.id')
            ->leftJoin('reservations', function ($join) use ($dateRange) {
                $join->on('reservations.shop_id', '=', 'shops.id')
                    ->whereRaw(
                        "STR_TO_DATE(CONCAT(reservations.reservation_date,' ',reservations.reservation_time), '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ?",
                        [$dateRange['startUtc'], $dateRange['endUtc']]
                    );
            })
            ->groupBy('areas.id', 'areas.name')
            ->orderBy('areas.id')
            ->get();
    }

    /**
     * 最新の店舗オーナーを取得
     */
    public function getLatestShopOwners(int $limit = 1): \Illuminate\Database\Eloquent\Collection
    {
        return ShopOwner::latest()
            ->take($limit)
            ->get(['id', 'name', 'email', 'created_at']);
    }

    /**
     * 最新の店舗を取得
     */
    public function getLatestShops(int $limit = 1): \Illuminate\Database\Eloquent\Collection
    {
        return Shop::latest()
            ->take($limit)
            ->get(['id', 'name', 'shop_owner_id', 'created_at'])
            ->load('shopOwner:id,name');
    }
}