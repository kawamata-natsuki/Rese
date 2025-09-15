<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Dashboard関連サービスをシングルトンとして登録
        $this->app->singleton(\App\Services\Admin\StatisticsRepository::class);
        $this->app->singleton(\App\Services\Admin\ChartDataService::class);
        $this->app->singleton(\App\Services\Admin\DashboardService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
