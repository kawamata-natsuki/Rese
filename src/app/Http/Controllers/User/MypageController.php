<?php

namespace App\Http\Controllers\User;

use App\Models\Shop;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 予約を日付＋時間の昇順で取得し、display_number を付与
        $reservations = $user->reservations()
            ->with('shop.area', 'shop.genre')
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get()
            ->values()
            ->map(function ($reservation, $index) {
                $reservation->display_number = $index + 1;
                return $reservation;
            });

        // ユーザーのお気に入り店舗取得
        $favoriteShops = $user->favoriteShops;

        return view('user.mypage.index', [
            'user' => $user,
            'reservations'  => $reservations,
            'favoriteShops' => $favoriteShops,
        ]);
    }
}
