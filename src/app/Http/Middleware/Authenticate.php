<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // /admin か /admin/... へのアクセス時は管理者ログインへ
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            // それ以外は通常ユーザのログインへ
            return route('login');
        };
    }
}
