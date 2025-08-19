<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with(['area', 'genre'])->get();
        return view('shop.index', compact('shops'));
    }

    public function show(Shop $shop, Request $request)
    {
        // 営業時間の取得
        $opening = $shop->opening_time->copy();
        $closing = $shop->closing_time;

        // 予約時刻用のスロット作成（30分刻み）
        $timeSlots = [];
        while ($opening < $closing) {
            $timeSlots[] = $opening->format('H:i');
            $opening->addMinutes(30);
        }

        // 予約人数用のスロット作成（～10人まで）
        $numberSlots = range(1, 10);

        // 戻るボタン
        $ref = $request->headers->get('referer');
        $fallback = route('shop.index');
        $backUrl = $fallback;

        if ($ref) {
            $refParts = parse_url($ref);
            $refHost  = $refParts['host'] ?? null;
            $refPath  = $refParts['path'] ?? '/';

            $currHost = $request->getHost();
            $currPath = $request->getPathInfo();

            $sameOrigin       = ($refHost === $currHost);
            $samePath         = rtrim($refPath, '/') === rtrim($currPath, '/');
            $isReservationFlow = str_contains($refPath, '/reservations');

            if ($sameOrigin && !$samePath && !$isReservationFlow) {
                $backUrl = $ref;
            }
        }

        return view('shop.show', compact('shop', 'timeSlots', 'numberSlots', 'backUrl'));
    }
}
