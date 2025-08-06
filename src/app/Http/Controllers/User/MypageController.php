<?php

namespace App\Http\Controllers\User;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 予約を日付＋時間の昇順で取得し、display_number を付与
        $reservations = $user->reservations()
            ->where('reservation_status', ReservationStatus::RESERVED->value)
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

        // 予約変更用モーダル内のスロット
        $dateSlots = collect();
        $dates = collect();
        $today = now();
        for ($i = 0; $i < 30; $i++) {
            $date = $today->copy()->addDays($i);
            $dates->push([
                'value' => $date->format('Y-m-d'),
                'label' => $date->format('Y年n月j日（' . ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] . '）'),
            ]);
        }
        $dateSlots = $dates;

        $timeSlots = [];
        if ($request->has('shop_id')) {
            $shop = Shop::findOrFail($request->shop_id);
            $opening = $shop->opening_time->copy();
            $closing = $shop->closing_time;
            while ($opening < $closing) {
                $timeSlots[] = $opening->format('H:i');
                $opening->addMinutes(30);
            }
        }

        $numberSlots = range(1, 10);

        return view('user.mypage.index', [
            'user' => $user,
            'reservations'  => $reservations,
            'favoriteShops' => $favoriteShops,
            'timeSlots'     => $timeSlots,
            'numberSlots'   => $numberSlots,
            'dateSlots'     => $dateSlots,
            'defaultGuests' => $reservation->number_of_guests ?? null,
        ]);
    }
}
