<?php

namespace App\Services\Admin;

use Carbon\Carbon;

class ChartDataService
{
    public function __construct(
        private StatisticsRepository $statisticsRepository
    ) {}

    /**
     * チャートデータを取得
     */
    public function getChartData(array $dateRange): array
    {
        return [
            'timeseries' => $this->getTimeSeriesData($dateRange),
            'pie' => $this->getPieChartData($dateRange),
        ];
    }

    /**
     * 時系列データを取得
     */
    private function getTimeSeriesData(array $dateRange): array
    {
        $labels = $this->makeDateLabels($dateRange['start'], $dateRange['end']);

        // 日別データを取得
        $reservationsByDay = $this->statisticsRepository->getReservationsByDay($dateRange);
        $reviewsByDay = $this->statisticsRepository->getReviewsByDay($dateRange);
        $usersByDay = $this->statisticsRepository->getUsersByDay($dateRange);

        return [
            'labels' => $labels,
            'reservations' => $this->fillSeriesByLabels($labels, $reservationsByDay),
            'reviews' => $this->fillSeriesByLabels($labels, $reviewsByDay),
            'users' => $this->fillSeriesByLabels($labels, $usersByDay),
        ];
    }

    /**
     * 円グラフデータを取得
     */
    private function getPieChartData(array $dateRange): array
    {
        // エリア別店舗数
        $areaShopCounts = $this->statisticsRepository->getShopsByArea();
        $pieLabels = $areaShopCounts->pluck('name')->all();
        $pieDataShops = $areaShopCounts->pluck('shops_count')
            ->map(fn($v) => (int)$v)
            ->all();

        // エリア別予約数（30日間）
        $areaReservationCounts = $this->statisticsRepository->getReservationsByArea($dateRange);
        $pieDataReservations = $areaReservationCounts->pluck('cnt')
            ->map(fn($v) => (int)$v)
            ->all();

        return [
            'labels' => $pieLabels,
            'shops' => $pieDataShops,
            'reservations_30d' => $pieDataReservations,
        ];
    }

    /**
     * 期間から日付ラベル配列を作成
     */
    private function makeDateLabels(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $cursor = clone $start;
        
        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }
        
        return $labels;
    }

    /**
     * ラベル順に並べ替え＆0埋め
     */
    private function fillSeriesByLabels(array $labels, array $map): array
    {
        $series = [];
        foreach ($labels as $date) {
            $series[] = (int)($map[$date] ?? 0);
        }
        return $series;
    }
}