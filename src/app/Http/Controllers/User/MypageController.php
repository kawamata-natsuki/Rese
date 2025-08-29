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

        // 今日と現在時刻（文字列）
        $today   = now()->toDateString();         // 'Y-m-d'
        $nowHms  = now()->format('H:i:s');        // 'H:i:s'

        // ===== 今後の予約（= RESERVED かつ 未来）=====
        $upcomingReservations = $user->reservations()
            ->where('reservation_status', ReservationStatus::RESERVED) // 未来は予約中のみ
            ->where(function ($q) use ($today, $nowHms) {
                $q->whereDate('reservation_date', '>', $today)
                    ->orWhere(function ($q2) use ($today, $nowHms) {
                        $q2->whereDate('reservation_date', $today)
                            ->whereRaw('TIME(COALESCE(reservation_time, "00:00:00")) >= ?', [$nowHms]);
                    });
            })
            ->with(['shop.area', 'shop.genre', 'review'])
            ->orderBy('reservation_date')
            ->orderByRaw('TIME(COALESCE(reservation_time, "00:00:00"))')
            ->get()
            ->values()
            ->map(function ($r, $idx) {
                $r->display_number = $idx + 1;
                return $r;
            });

        // ===== 過去の予約（= VISITED すべて ＋ RESERVED で過去のもの）=====
        $pastReservations = $user->reservations()
            ->where(function ($q) use ($today, $nowHms) {
                // 来店済みはすべて過去扱い
                $q->where('reservation_status', ReservationStatus::VISITED)
                    // もしくは、まだRESERVEDだが期日・時刻を過ぎたもの
                    ->orWhere(function ($q2) use ($today, $nowHms) {
                        $q2->where('reservation_status', ReservationStatus::RESERVED)
                            ->where(function ($qq) use ($today, $nowHms) {
                                $qq->whereDate('reservation_date', '<', $today)
                                    ->orWhere(function ($qq2) use ($today, $nowHms) {
                                        $qq2->whereDate('reservation_date', $today)
                                            ->whereRaw('TIME(COALESCE(reservation_time, "00:00:00")) < ?', [$nowHms]);
                                    });
                            });
                    });
            })
            ->with(['shop.area', 'shop.genre', 'review'])
            ->orderBy('reservation_date', 'desc')
            ->orderByRaw('TIME(COALESCE(reservation_time, "00:00:00")) desc')
            ->take(10)
            ->get()
            ->map(function ($r) {
                // レビュー待ちは「来店済み かつ レビュー未投稿」のみに限定
                $r->isReviewPending = $r->reservation_status === ReservationStatus::VISITED
                    && $r->review === null;
                return $r;
            });

        // お気に入り
        $favoriteShops = $user->favoriteShops()
            ->with(['area:id,name', 'genre:id,name'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();

        // 予約変更モーダル用
        $dates = collect();
        $todayDt = now();
        for ($i = 0; $i < 30; $i++) {
            $d = $todayDt->copy()->addDays($i);
            $dates->push([
                'value' => $d->format('Y-m-d'),
                'label' => $d->format('Y年n月j日（' . ['日', '月', '火', '水', '木', '金', '土'][$d->dayOfWeek] . '）'),
            ]);
        }
        $numberSlots = range(1, 10);

        return view('user.mypage.index', [
            'user'                 => $user,
            'upcomingReservations' => $upcomingReservations,
            'pastReservations'     => $pastReservations,
            'favoriteShops'        => $favoriteShops,
            'numberSlots'          => $numberSlots,
            'dateSlots'            => $dates,
        ]);
    }
}
