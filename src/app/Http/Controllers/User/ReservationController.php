<?php

namespace App\Http\Controllers\User;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Models\Reservation;
use App\Models\Shop;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\URL;

class ReservationController extends Controller
{
    public function store(ReservationRequest $request)
    {
        $shop = Shop::findOrFail($request->shop_id);

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

    // 予約変更処理
    public function update(ReservationRequest $request, Reservation $reservation)
    {
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

    // 予約キャンセル処理
    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        // ステータスをキャンセルに更新
        $reservation->reservation_status = ReservationStatus::CANCELLED;
        $reservation->save();

        return redirect()->back()->with('success', '予約をキャンセルしました');
    }

    // QRコード生成・表示処理
    public function qr(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        $baseUrl = config('app.qr_base_url');

        $signatureUrl = URL::signedRoute(
            'shop.checkin',
            ['reservation_id' => $reservation->id],
            now()->addMinutes(5)
        );

        $signature = parse_url($signatureUrl, PHP_URL_QUERY);

        $qrContent = $baseUrl . '/shop/checkin?' . $signature;

        $builder = new Builder(
            writer: new PngWriter(),
            data: $qrContent,
            size: 200
        );

        $result = $builder->build();

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType());
    }
}
