<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function checkin(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'リンクが無効か期限切れです');
        }

        $reservation = Reservation::find($request->reservation_id);

        if (! $reservation || !$reservation->reservation_date->isToday()) {
            return redirect()->route('shop.dashboard')->with('error', '有効な予約が見つかりません');
        }

        if ($reservation->checked_in) {
            return redirect()->route('shop.dashboard')->with('message', 'すでにチェックイン済みです');
        }

        $reservation->checked_in = true;
        $reservation->checked_in_at = now();
        $reservation->save();

        return redirect()->route('shop.dashboard')->with('message', 'チェックイン完了！');
    }
}
