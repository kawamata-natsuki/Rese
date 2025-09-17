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
        // === 期間設定（アプリTZ基準） ===
        $tz    = config('app.timezone', 'Asia/Tokyo');
        $end   = Carbon::now($tz)->endOfDay();            // 今日の終わり
        $start = (clone $end)->subDays(29)->startOfDay(); // 30日分（過去29日 + 今日）

        // DBはUTC想定なので、境界をUTCに変換して渡す
        $startUtc = $start->copy()->timezone('UTC');
        $endUtc   = $end->copy()->timezone('UTC');

        // === カード（直近30日） ===
        $users30d = User::whereBetween(
            'created_at',
            [
                $startUtc,
                $endUtc
            ]
        )->count();

        $reservations30d = Reservation::whereBetween(
            'created_at',
            [
                $startUtc,
                $endUtc
            ]
        )->count();

        $reviews30d = Review::whereBetween(
            'created_at',
            [
                $startUtc,
                $endUtc
            ]
        )->whereNull('deleted_at')->count();

        // キャンセル/無断キャンセル（30日）
        $cancelledReservations = Reservation::whereBetween('created_at', [$startUtc, $endUtc])
            ->where('reservation_status', 'cancelled')
            ->count();
        $noShowReservations = Reservation::whereBetween('created_at', [$startUtc, $endUtc])
            ->where('reservation_status', 'no-show')
            ->count();

        // キャンセル率（%）
        $cancellationRate = $reservations30d > 0
            ? round(($cancelledReservations / $reservations30d) * 100, 1)
            : 0;
        // 無断キャンセル率（%）
        $noShowRate = $reservations30d > 0
            ? round(($noShowReservations / $reservations30d) * 100, 1)
            : 0;

        // === 時系列（30日） ===
        $labels = $this->makeDateLabels($start, $end); // ["Y-m-d", ...]

        // 予約：結合日時 -> 日付で集計
        $reservationsByDay = Reservation::selectRaw('reservation_date as d, COUNT(*) as c')
            ->whereBetween('reservation_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('reservation_date')
            ->orderBy('reservation_date')
            ->pluck('c', 'd')
            ->all();

        // no-show：日別件数
        $noShowByDay = Reservation::selectRaw("reservation_date as d, COUNT(*) as c")
            ->whereBetween('reservation_date', [$start->toDateString(), $end->toDateString()])
            ->where('reservation_status', 'no-show')
            ->groupBy('reservation_date')
            ->orderBy('reservation_date')
            ->pluck('c', 'd')
            ->all();

        // レビュー／ユーザー：created_at -> 日付で集計（ローカル時刻基準）
        $reviewsByDay = Review::selectRaw("DATE(created_at) as d, COUNT(*) as c")
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->groupBy('d')->orderBy('d')->pluck('c', 'd')->all();

        $usersByDay = User::selectRaw("DATE(created_at) as d, COUNT(*) as c")
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->groupBy('d')->orderBy('d')->pluck('c', 'd')->all();

        // ラベル順に0埋め
        $seriesReservations = $this->fillSeriesByLabels($labels, $reservationsByDay);
        $seriesNoShowCount  = $this->fillSeriesByLabels($labels, $noShowByDay);

        // no-show率（%）：no-show件数 / 予約総数 * 100
        $seriesNoShowRate = [];
        foreach ($seriesReservations as $i => $total) {
            $ns = (int)($seriesNoShowCount[$i] ?? 0);
            $seriesNoShowRate[] = $total > 0 ? round(($ns / $total) * 100, 1) : 0;
        }
        // no-show率 7日移動平均（端は利用可能な範囲で平均）
        $seriesNoShowRateMA7 = [];
        $window = 7;
        $rollingSum = 0.0;
        for ($i = 0; $i < count($seriesNoShowRate); $i++) {
            $rollingSum += (float) $seriesNoShowRate[$i];
            if ($i >= $window) {
                $rollingSum -= (float) $seriesNoShowRate[$i - $window];
            }
            $den = min($i + 1, $window);
            $seriesNoShowRateMA7[] = $den > 0 ? round($rollingSum / $den, 1) : 0.0;
        }
        $seriesReviews      = $this->fillSeriesByLabels($labels, $reviewsByDay);
        $seriesUsers        = $this->fillSeriesByLabels($labels, $usersByDay);

        // === アクティブ稼働率（直近30日 / 5件以上 / キャンセル除外） ===
        $threshold = 5;

        // 総店舗数（公開店舗だけにしたいなら where('published',1) など条件追加）
        $totalShops = Shop::count();

        // アクティブ店舗の shop_id を抽出
        $activeShopIds = Reservation::query()
            ->whereBetween('reservation_date', [$startUtc, $endUtc])
            ->whereNotIn('reservation_status', ['cancelled', 'no-show'])
            ->groupBy('shop_id')
            ->havingRaw('COUNT(*) >= ?', [$threshold])
            ->pluck('shop_id');

        $activeShops = Shop::whereIn('id', $activeShopIds)->count();
        $activeRate  = $totalShops > 0 ? round($activeShops / $totalShops * 100, 1) : 0.0;

        // 休眠店舗（参考：画面右の小カードやリンクに便利）
        $dormantShops = max($totalShops - $activeShops, 0);

        // 総オーナー数
        $totalShopOwners = ShopOwner::count();

        // 直近30日の新規店舗 / 新規オーナー
        $shops30d = Shop::whereBetween('created_at', [$startUtc, $endUtc])->count();
        $owners30d = ShopOwner::whereBetween('created_at', [$startUtc, $endUtc])->count();

        // === 円グラフ（エリア別） ===
        // 1) 店舗数
        $areaShopCounts = Area::withCount('shops')->get();
        $pieLabels    = $areaShopCounts->pluck('name')->all();
        $pieDataShops = $areaShopCounts->pluck('shops_count')->map(fn($v) => (int)$v)->all();

        // 2) 直近30日の予約数（エリア別） - reservations に reserved_at は無いので結合式で集計
        $areaReservationCounts = DB::table('areas')
            ->selectRaw('areas.name as area, COUNT(reservations.id) as cnt')
            ->leftJoin('shops', 'shops.area_id', '=', 'areas.id')
            ->leftJoin('reservations', function ($join) use ($startUtc, $endUtc) {
                $join->on('reservations.shop_id', '=', 'shops.id')
                    ->whereRaw(
                        "STR_TO_DATE(CONCAT(reservations.reservation_date,' ',reservations.reservation_time), '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ?",
                        [$startUtc, $endUtc]
                    );
            })
            ->groupBy('areas.id', 'areas.name')
            ->orderBy('areas.id')
            ->get();

        $pieDataReservations = $areaReservationCounts->pluck('cnt')->map(fn($v) => (int)$v)->all();

        // === 最新リスト（N+1防止） ===
        $latestShopOwners = ShopOwner::latest()
            ->take(1)
            ->get(['id', 'name', 'email', 'created_at']);

        $latestShops = Shop::latest()
            ->take(1)
            ->get(['id', 'name', 'shop_owner_id', 'created_at'])
            ->load('shopOwner:id,name');

        // ★ 直近30日の平均評価（deleted除外）
        $ratingColumn = 'rating';
        $avgRating30d = Review::whereBetween('created_at', [$startUtc, $endUtc])
            ->whereNull('deleted_at')
            ->avg($ratingColumn);

        // null対策 & 範囲クリップ（1〜5想定。必要に応じて調整）
        $avgRating30d = (float)($avgRating30d ?? 0.0);
        $avgRating30d = max(0, min(5, $avgRating30d));

        /* ----------------------------------------------------------------
     * 2) Top Shops (30D) 予約数トップ5 + キャンセル率
     *    - total: 期間内の予約件数
     *    - cancelled: 期間内キャンセル件数
     *    - cancel_rate: 小数1桁 %
     * ---------------------------------------------------------------- */
        $topShops30d = DB::table('shops')
            ->leftJoin('reservations', function ($join) use ($startUtc, $endUtc) {
                $join->on('reservations.shop_id', '=', 'shops.id')
                    ->whereBetween('reservations.created_at', [$startUtc, $endUtc])
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

        /* ----------------------------------------------------------------
     * 3) Inactive Shops (0 in 30D)
     *    期間内予約が0件の店舗数（ソフトデリート除外）
     * ---------------------------------------------------------------- */
        $inactiveShops30d = DB::table('shops')
            ->whereNull('shops.deleted_at')
            ->whereNotExists(function ($q) use ($startUtc, $endUtc) {
                $q->select(DB::raw(1))
                    ->from('reservations')
                    ->whereColumn('reservations.shop_id', 'shops.id')
                    ->whereBetween('reservations.created_at', [$startUtc, $endUtc])
                    ->whereNull('reservations.deleted_at');
            })
            ->count();

        // === ビューへ ===
        // 未読通知件数（ダミー: ここでは0を渡し、今後DB通知と連携）
        $unreadCount = 0;

        return view('admin.dashboard.index', [
            // 数字カード
            'users30d'       => $users30d,
            'reservations30d' => $reservations30d,
            'reviews30d'     => $reviews30d,
            'cancellationRate' => $cancellationRate,
            'noShowRate'       => $noShowRate,
            'activeRate'        => $activeRate,
            'activeShops'       => $activeShops,
            'totalShops'        => $totalShops,
            'dormantShops'      => $dormantShops,
            'activeThreshold'   => $threshold,
            'avgRating30d'    => $avgRating30d,
            'topShops30d'       => $topShops30d,
            'inactiveShops30d'  => $inactiveShops30d,
            'totalShopOwners'    => $totalShopOwners,
            'shops30d'         => $shops30d,
            'owners30d'        => $owners30d,

            // 最新
            'latestShopOwners' => $latestShopOwners,
            'latestShops'      => $latestShops,

            // グラフ
            'charts' => [
                'timeseries' => [
                    'labels'       => $labels,
                    'reservations' => $seriesReservations,
                    'reviews'      => $seriesReviews,
                    'users'        => $seriesUsers,
                    'noShowRate'   => $seriesNoShowRate,
                    'noShowRateMA7' => $seriesNoShowRateMA7,
                    'noShowCount'  => $seriesNoShowCount,
                    'noShowRate30d' => $noShowRate,
                ],
                'pie' => [
                    'labels'           => $pieLabels,
                    'shops'            => $pieDataShops,
                    'reservations_30d' => $pieDataReservations,
                ],
            ],
            'unreadCount'        => $unreadCount,
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
}
