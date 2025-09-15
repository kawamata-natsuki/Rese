<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;

class AdminDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * 管理者ダッシュボード表示
     */
    public function index()
    {
        $data = $this->dashboardService->getDashboardData();

        return view('admin.dashboard.index', [
            // 基本統計
            'users30d' => $data['stats']['users30d'],
            'reservations30d' => $data['stats']['reservations30d'],
            'reviews30d' => $data['stats']['reviews30d'],
            'cancellationRate' => $data['stats']['cancellationRate'],
            'avgRating30d' => $data['stats']['avgRating30d'],

            // 追加メトリクス
            'activeRate' => $data['metrics']['activeRate'],
            'activeShops' => $data['metrics']['activeShops'],
            'totalShops' => $data['metrics']['totalShops'],
            'dormantShops' => $data['metrics']['dormantShops'],
            'activeThreshold' => $data['metrics']['activeThreshold'],
            'topShops30d' => $data['metrics']['topShops30d'],
            'inactiveShops30d' => $data['metrics']['inactiveShops30d'],

            // 最新データ
            'latestShopOwners' => $data['latest']['latestShopOwners'],
            'latestShops' => $data['latest']['latestShops'],

            // チャートデータ
            'charts' => $data['charts'],
        ]);
    }
}
