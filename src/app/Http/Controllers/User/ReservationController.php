<?php

namespace App\Http\Controllers\User;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Models\Reservation;
use App\Models\Shop;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class ReservationController extends Controller
{
    public function store(ReservationRequest $request)
    {
        $shop = Shop::findOrFail($request->shop_id);

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
            'user_id' => auth()->id(),
            'shop_id' => $validated['shop_id'],
            'reservation_date' => $validated['date'],
            'reservation_time' => $validated['time'],
            'number_of_guests' => $validated['number'],
        ]);

        return redirect()->route('user.reservations.done');
    }

    public function done()
    {
        return view('user.reservations.done');
    }

    public function update(ReservationRequest $request, Reservation $reservation)
    {
        // 予約変更処理
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();

        $reservation->update([
            'reservation_date' => $validated['date'],
            'reservation_time' => $validated['time'],
            'number_of_guests' => $validated['number'],
        ]);

        return redirect()->back()->with('success', '予約を変更しました');
    }

    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        // ステータスをキャンセルに更新
        $reservation->reservation_status = ReservationStatus::CANCELED;
        $reservation->save();

        return redirect()->back()->with('success', '予約をキャンセルしました');
    }

    public function qr(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        $qrContent = "予約番号: {$reservation->id}\n"
            . "日付: {$reservation->reservation_date->format('Y-m-d')}\n"
            . "時間: {$reservation->reservation_time->format('H:i')}";

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($qrContent);

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }
}
