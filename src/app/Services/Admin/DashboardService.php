<?php

namespace App\Services\Admin;

use App\Services\Admin\StatisticsRepository;
use App\Services\Admin\ChartDataService;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private StatisticsRepository $statisticsRepository,
        private ChartDataService $chartDataService
    ) {}

    /**
     * ダッシュボードデータを取得
     */
    public function getDashboardData(): array
    {
        $dateRange = $this->getDateRange();
        
        return [
            // 基本統計
            'stats' => $this->getBasicStats($dateRange),
            
            // チャートデータ
            'charts' => $this->chartDataService->getChartData($dateRange),
            
            // 最新データ
            'latest' => $this->getLatestData(),
            
            // その他の指標
            'metrics' => $this->getAdditionalMetrics($dateRange),
        ];
    }

    /**
     * 日付範囲を取得（過去30日間）
     */
    private function getDateRange(): array
    {
        $tz = config('app.timezone', 'Asia/Tokyo');
        $end = Carbon::now($tz)->endOfDay();
        $start = (clone $end)->subDays(29)->startOfDay();

        return [
            'start' => $start,
            'end' => $end,
            'startUtc' => $start->copy()->timezone('UTC'),
            'endUtc' => $end->copy()->timezone('UTC'),
        ];
    }

    /**
     * 基本統計データを取得
     */
    private function getBasicStats(array $dateRange): array
    {
        return [
            'users30d' => $this->statisticsRepository->getUsersCount($dateRange),
            'reservations30d' => $this->statisticsRepository->getReservationsCount($dateRange),
            'reviews30d' => $this->statisticsRepository->getReviewsCount($dateRange),
            'cancellationRate' => $this->statisticsRepository->getCancellationRate($dateRange),
            'avgRating30d' => $this->statisticsRepository->getAverageRating($dateRange),
        ];
    }

    /**
     * 追加メトリクスを取得
     */
    private function getAdditionalMetrics(array $dateRange): array
    {
        $activeData = $this->statisticsRepository->getActiveShopsData($dateRange);
        
        return [
            'activeRate' => $activeData['activeRate'],
            'activeShops' => $activeData['activeShops'],
            'totalShops' => $activeData['totalShops'],
            'dormantShops' => $activeData['dormantShops'],
            'activeThreshold' => $activeData['threshold'],
            'topShops30d' => $this->statisticsRepository->getTopShops($dateRange),
            'inactiveShops30d' => $this->statisticsRepository->getInactiveShopsCount($dateRange),
        ];
    }

    /**
     * 最新データを取得
     */
    private function getLatestData(): array
    {
        return [
            'latestShopOwners' => $this->statisticsRepository->getLatestShopOwners(),
            'latestShops' => $this->statisticsRepository->getLatestShops(),
        ];
    }
}