<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    // 署名付き（QR経由）
    public function checkin(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'リンクが無効か期限切れです');
        }

        $validated = $request->validate([
            'reservation_id' => ['required', 'integer', 'min:1'],
        ]);

        $reservation = Reservation::query()->find($request->integer('reservation_id'));
        if (!$reservation) {
            return redirect()->route('shop.dashboard')->with('error', '予約が見つかりません');
        }

        // 店の所有権チェック：ログイン中の店の予約か？
        if ($request->user('shop')->id !== $reservation->shop_id) {
            abort(403, '権限がありません');
        }

        if (!$reservation->isForToday()) {
            return redirect()->route('shop.dashboard')->with('error', '本日の予約ではありません');
        }

        if ($reservation->is_visited) {
            return redirect()->route('shop.dashboard')->with('message', 'すでにチェックイン済みです');
        }

        $this->markVisitedAndNotify($reservation);

        return redirect()->route('shop.dashboard')->with('message', 'チェックイン完了！');
    }

    // 手動チェックイン
    public function __invoke(Reservation $reservation, Request $request)
    {
        // 所有権チェック
        if ($request->user('shop')->id !== $reservation->shop_id) {
            abort(403, '権限がありません');
        }

        if ($reservation->is_visited) {
            return back()->with('message', 'すでにチェックイン済みです');
        }

        $this->markVisitedAndNotify($reservation);

        return back()->with('success', '来店を記録しました');
    }

    private function markVisitedAndNotify(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            // 予約行をロックして二重実行を防ぐ
            $res = Reservation::whereKey($reservation->id)
                ->lockForUpdate()
                ->first();

            // すでにチェックイン済み（他リクエストが先行）
            if ($res->visited_at || $res->reservation_status === \App\Enums\ReservationStatus::VISITED) {
                return;
            }

            // 来店確定
            $res->fill([
                'visited_at'         => now(),
                'reservation_status' => \App\Enums\ReservationStatus::VISITED,
            ])->save();

            // 未レビューなら通知
            if (!$res->review()->exists()) {
                // さらに堅くするなら既存通知チェックも：
                $exists = $res->user->notifications()
                    ->where('type', \App\Notifications\ReviewReminder::class)
                    ->where('data->reservation', $res->id)
                    ->exists();

                if (!$exists) {
                    $res->user->notify(new \App\Notifications\ReviewReminder($res)); // ShouldQueueを推奨
                }
            }
        });
    }
}
