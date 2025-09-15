<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopOwner;
use App\Models\User;
use App\Models\Area;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $range = $this->getLast30DaysRange();

        // 集計
        $cards       = $this->getSummaryCards($range);
        $timeseries  = $this->getTimeSeries($range);
        $active      = $this->getActiveStats($range);
        $pie         = $this->getAreaPieData($range);
        $latest      = $this->getLatestEntities();
        $avgRating30d = $this->getReviewAverage($range);
        $shopStats   = $this->getShopStats($range);

        // ビューへ
        return view('admin.dashboard.index', [
            // 数字カード
            'users30d'         => $cards['users30d'],
            'reservations30d'  => $cards['reservations30d'],
            'reviews30d'       => $cards['reviews30d'],
            'cancellationRate' => $cards['cancellationRate'],

            // 稼働率
            'activeRate'      => $active['activeRate'],
            'activeShops'     => $active['activeShops'],
            'totalShops'      => $active['totalShops'],
            'dormantShops'    => $active['dormantShops'],
            'activeThreshold' => $active['activeThreshold'],

            // レビュー平均
            'avgRating30d'    => $avgRating30d,

            // ランキング等
            'topShops30d'      => $shopStats['topShops30d'],
            'inactiveShops30d' => $shopStats['inactiveShops30d'],

            // 最新
            'latestShopOwners' => $latest['latestShopOwners'],
            'latestShops'      => $latest['latestShops'],

            // グラフ
            'charts' => [
                'timeseries' => $timeseries,
                'pie'        => $pie,
            ],
        ]);
    }

    /**
     * 期間から "Y-m-d" ラベル配列を作る（両端含む）
     * @return array<int,string>
     */
    private function makeDateLabels(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $cursor = (clone $start);
        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }
        return $labels;
    }

    /**
     * ['Y-m-d' => count] をラベル順に並べ替え＆0埋め
     * @param array<int,string> $labels
     * @param array<string,int> $map e.g. ['2025-09-01' => 3]
     * @return array<int,int>
     */
    private function fillSeriesByLabels(array $labels, array $map): array
    {
        $series = [];
        foreach ($labels as $d) {
            $series[] = (int)($map[$d] ?? 0);
        }
        return $series;
    }

    /**
     * 直近30日レンジ（アプリTZとUTC）
     * @return array{tz:string,start:Carbon,end:Carbon,startUtc:Carbon,endUtc:Carbon}
     */
    private function getLast30DaysRange(): array
    {
        $tz    = config('app.timezone', 'Asia/Tokyo');
        $end   = Carbon::now($tz)->endOfDay();
        $start = (clone $end)->subDays(29)->startOfDay();

        return [
            'tz' => $tz,
            'start' => $start,
            'end' => $end,
            'startUtc' => $start->copy()->timezone('UTC'),
            'endUtc' => $end->copy()->timezone('UTC'),
        ];
    }

    /**
     * トップの数値カード
     * @param array{startUtc:Carbon,endUtc:Carbon} $range
     * @return array{users30d:int,reservations30d:int,reviews30d:int,cancellationRate:float}
     */
    private function getSummaryCards(array $range): array
    {
        $users30d = User::whereBetween('created_at', [$range['startUtc'], $range['endUtc']])->count();
        $reservations30d = Reservation::whereBetween('created_at', [$range['startUtc'], $range['endUtc']])->count();
        $reviews30d = Review::whereBetween('created_at', [$range['startUtc'], $range['endUtc']])
            ->whereNull('deleted_at')
            ->count();

        $cancelledReservations = Reservation::whereBetween('created_at', [$range['startUtc'], $range['endUtc']])
            ->where('reservation_status', 'cancelled')
            ->count();

        $cancellationRate = $reservations30d > 0
            ? round(($cancelledReservations / $reservations30d) * 100, 1)
            : 0.0;

        return compact('users30d', 'reservations30d', 'reviews30d', 'cancellationRate');
    }

    /**
     * 折れ線グラフ用データ
     * @param array{start:Carbon,end:Carbon,startUtc:Carbon,endUtc:Carbon} $range
     * @return array{labels:array,reservations:array,reviews:array,users:array}
     */
    private function getTimeSeries(array $range): array
    {
        $labels = $this->makeDateLabels($range['start'], $range['end']);

        $reservationsByDay = Reservation::selectRaw('reservation_date as d, COUNT(*) as c')
            ->whereBetween('reservation_date', [$range['start']->toDateString(), $range['end']->toDateString()])
            ->groupBy('reservation_date')
            ->orderBy('reservation_date')
            ->pluck('c', 'd')
            ->all();

        $reviewsByDay = Review::selectRaw("DATE(created_at) as d, COUNT(*) as c")
            ->whereBetween('created_at', [$range['startUtc'], $range['endUtc']])
            ->groupBy('d')->orderBy('d')->pluck('c', 'd')->all();

        $usersByDay = User::selectRaw("DATE(created_at) as d, COUNT(*) as c")
            ->whereBetween('created_at', [$range['startUtc'], $range['endUtc']])
            ->groupBy('d')->orderBy('d')->pluck('c', 'd')->all();

        return [
            'labels' => $labels,
            'reservations' => $this->fillSeriesByLabels($labels, $reservationsByDay),
            'reviews' => $this->fillSeriesByLabels($labels, $reviewsByDay),
            'users' => $this->fillSeriesByLabels($labels, $usersByDay),
        ];
    }

    /**
     * 稼働率など
     * @param array{start:Carbon,end:Carbon} $range
     * @return array{activeRate:float,activeShops:int,totalShops:int,dormantShops:int,activeThreshold:int}
     */
    private function getActiveStats(array $range): array
    {
        $threshold = 5;

        $totalShops = Shop::count();

        $activeShopIds = Reservation::query()
            ->whereBetween('reservation_date', [$range['start']->toDateString(), $range['end']->toDateString()])
            ->where('reservation_status', '!=', 'cancelled')
            ->groupBy('shop_id')
            ->havingRaw('COUNT(*) >= ?', [$threshold])
            ->pluck('shop_id');

        $activeShops = Shop::whereIn('id', $activeShopIds)->count();
        $activeRate  = $totalShops > 0 ? round($activeShops / $totalShops * 100, 1) : 0.0;
        $dormantShops = max($totalShops - $activeShops, 0);

        return compact('activeRate', 'activeShops', 'totalShops', 'dormantShops', 'activeThreshold');
    }

    /**
     * エリア別円グラフ
     * @param array{startUtc:Carbon,endUtc:Carbon} $range
     * @return array{labels:array,shops:array,reservations_30d:array}
     */
    private function getAreaPieData(array $range): array
    {
        $areaShopCounts = Area::withCount('shops')->get();
        $labels    = $areaShopCounts->pluck('name')->all();
        $shopsData = $areaShopCounts->pluck('shops_count')->map(fn($v) => (int)$v)->all();

        $areaReservationCounts = DB::table('areas')
            ->selectRaw('areas.name as area, COUNT(reservations.id) as cnt')
            ->leftJoin('shops', 'shops.area_id', '=', 'areas.id')
            ->leftJoin('reservations', function ($join) use ($range) {
                $join->on('reservations.shop_id', '=', 'shops.id')
                    ->whereRaw(
                        "STR_TO_DATE(CONCAT(reservations.reservation_date,' ',reservations.reservation_time), '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ?",
                        [$range['startUtc'], $range['endUtc']]
                    );
            })
            ->groupBy('areas.id', 'areas.name')
            ->orderBy('areas.id')
            ->get();

        $reservations30d = $areaReservationCounts->pluck('cnt')->map(fn($v) => (int)$v)->all();

        return [
            'labels' => $labels,
            'shops' => $shopsData,
            'reservations_30d' => $reservations30d,
        ];
    }

    /**
     * 最新エンティティ
     * @return array{latestShopOwners:\Illuminate\Support\Collection,latestShops:\Illuminate\Support\Collection}
     */
    private function getLatestEntities(): array
    {
        $latestShopOwners = ShopOwner::latest()
            ->take(1)
            ->get(['id', 'name', 'email', 'created_at']);

        $latestShops = Shop::latest()
            ->take(1)
            ->get(['id', 'name', 'shop_owner_id', 'created_at'])
            ->load('shopOwner:id,name');

        return compact('latestShopOwners', 'latestShops');
    }

    /**
     * 30日平均評価
     * @param array{startUtc:Carbon,endUtc:Carbon} $range
     */
    private function getReviewAverage(array $range): float
    {
        $avg = Review::whereBetween('created_at', [$range['startUtc'], $range['endUtc']])
            ->whereNull('deleted_at')
            ->avg('rating');

        $avg = (float)($avg ?? 0.0);
        return max(0, min(5, $avg));
    }

    /**
     * トップ店舗と非稼働店舗数
     * @param array{startUtc:Carbon,endUtc:Carbon} $range
     * @return array{topShops30d:\Illuminate\Support\Collection,inactiveShops30d:int}
     */
    private function getShopStats(array $range): array
    {
        $topShops30d = DB::table('shops')
            ->leftJoin('reservations', function ($join) use ($range) {
                $join->on('reservations.shop_id', '=', 'shops.id')
                    ->whereBetween('reservations.created_at', [$range['startUtc'], $range['endUtc']])
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
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $row->cancel_rate = $row->total > 0 ? round(($row->cancelled / $row->total) * 100, 1) : 0.0;
                return $row;
            });

        $inactiveShops30d = DB::table('shops')
            ->whereNull('shops.deleted_at')
            ->whereNotExists(function ($q) use ($range) {
                $q->select(DB::raw(1))
                    ->from('reservations')
                    ->whereColumn('reservations.shop_id', 'shops.id')
                    ->whereBetween('reservations.created_at', [$range['startUtc'], $range['endUtc']])
                    ->whereNull('reservations.deleted_at');
            })
            ->count();

        return compact('topShops30d', 'inactiveShops30d');
    }
}
