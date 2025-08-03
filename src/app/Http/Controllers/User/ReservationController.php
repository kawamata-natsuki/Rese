<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Models\Reservation;
use App\Models\Shop;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // 予約処理
    public function store(ReservationRequest $request)
    {
        $shop = Shop::findOrFail($request->shop_id);

        // スロットを動的に生成
        $opening = $shop->opening_time->copy();
        $closing = $shop->closing_time;

        $timeSlots = [];
        while ($opening < $closing) {
            $timeSlots[] = $opening->format('H:i');
            $opening->addMinutes(30);
        }

        $numberSlots = range(1, 10);

        $validated = $request->validated();
        Reservation::create([
            'user_id'  => auth()->id(),
            'shop_id'  => $validated['shop_id'],
            'reservation_date' => $validated['date'],
            'reservation_time' => $validated['time'],
            'number_of_guests' => $validated['number'],
        ]);

        return redirect()
            ->route('user.reservations.done');
    }

    public function done()
    {
        return view('user.reservations.done');
    }

    public function update()
    {
        // 予約変更処理
    }

    public function destroy()
    {
        // 予約キャンセル処理
    }
}
