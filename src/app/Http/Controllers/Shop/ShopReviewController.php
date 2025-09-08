<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopReviewController extends Controller
{
    public function index(Shop $shop, Request $request)
    {
        // 並び替え
        $sort = $request->get('sort', 'recent');
        if ($sort === 'new') $sort = 'recent';

        $query = $shop->reviews()->with('user');
        switch ($sort) {
            case 'rating':
                $query->orderByDesc('rating')->orderByDesc('created_at');
                break;
            case 'lowrate':
                $query->orderBy('rating')->orderByDesc('created_at');
                break;
            default:
                $query->orderByDesc('created_at'); // recent
        }
        $reviews = $query->paginate(10);

        // ★平均 & 件数
        $avgRating    = round($shop->reviews()->avg('rating') ?? 0, 1);

        // 生の件数 [rating => count]
        $raw = $shop->reviews()
            ->selectRaw('rating, COUNT(*) as c')
            ->groupBy('rating')
            ->pluck('c', 'rating');

        // 件数（5→1で0埋め）
        $counts = [];
        for ($i = 5; $i >= 1; $i--) {
            $counts[$i] = (int)($raw[$i] ?? 0);
        }
        $reviewsCount = array_sum($counts);

        // パーセンテージ（5→1の順を維持）
        $distribution = collect($counts)->map(function ($c) use ($reviewsCount) {
            return $reviewsCount > 0 ? round($c * 100 / $reviewsCount) : 0;
        });

        return view('shop.reviews.index', compact(
            'shop',
            'reviews',
            'avgRating',
            'reviewsCount',
            'distribution',
            'counts',          // ← これを渡す！
            'sort'
        ));
    }
}
