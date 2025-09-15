<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 管理画面レイアウトに未読件数を共有
        View::composer('admin.layouts.app', function ($view) {
            $unread = 0;
            try {
                $guardUser = Auth::guard('admin')->user();
                if ($guardUser && method_exists($guardUser, 'unreadNotifications')) {
                    $unread = (int) $guardUser->unreadNotifications()->count();
                }
            } catch (\Throwable $e) {
                // 失敗時はゼロのまま共有（ログは必要に応じて）
            }

            $view->with('unreadCount', $unread);
        });
    }
}
