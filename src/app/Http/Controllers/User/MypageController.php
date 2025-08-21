<?php

namespace App\Http\Controllers\User;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $now  = now();

        // ベースクエリ
        $base = $user->reservations()
            ->where('reservation_status', ReservationStatus::RESERVED->value)
            ->with(['shop.area', 'shop.genre', 'review']);

        // 今後の予約（= きょう以降）
        $upcomingReservations = (clone $base)
            ->whereRaw("CONCAT(reservation_date, ' ', reservation_time) >= ?", [$now])
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get()
            ->values()
            ->map(function ($reservation, $idx) {
                $reservation->display_number = $idx + 1;
                return $reservation;
            });

        // 過去の予約（直近10件/降順）
        $pastReservations = (clone $base)
            ->whereRaw("CONCAT(reservation_date, ' ', reservation_time) < ?", [$now->format('Y-m-d H:i:s')])
            ->orderBy('reservation_date', 'desc')
            ->orderBy('reservation_time', 'desc')
            ->take(10)
            ->get()
            ->map(
                function ($r) {
                    $startsAtPast = $r->startsAt()->isPast();
                    $hasReview    = $r->review !== null;

                    // “レビュー待ち”の定義
                    $r->isReviewPending = $startsAtPast && !$hasReview;

                    return $r;
                }
            );

        // ユーザーのお気に入り店舗取得
        $favoriteShops = $user->favoriteShops()
            ->with(['area:id,name', 'genre:id,name'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();

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

        $numberSlots = range(1, 10);

        return view('user.mypage.index', [
            'user'                => $user,
            'upcomingReservations' => $upcomingReservations,
            'pastReservations'    => $pastReservations,
            'favoriteShops'       => $favoriteShops,
            'numberSlots'         => $numberSlots,
            'dateSlots'           => $dateSlots,
        ]);
    }
}
