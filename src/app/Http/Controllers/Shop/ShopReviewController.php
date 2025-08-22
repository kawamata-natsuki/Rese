<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopReviewController extends Controller
{
    public function index(Shop $shop, Request $request)
    {
        $sort = $request->get('sort', 'new');
        $q = $shop->reviews()->with('user');
        if ($sort === 'high')
            $q->orderByDesc('rating')->orderByDesc('id');
        elseif ($sort === 'low')
            $q->orderBy('rating')->orderByDesc('id');
        else
            $q->latest();

        $reviews = $q->paginate(10)->withQueryString();
        $avgRating = round($shop->reviews()->avg('rating') ?? 0, 1);
        $reviewsCount = $shop->reviews()->count();

        return view('shop.reviews.index', compact('shop', 'reviews', 'sort', 'avgRating', 'reviewsCount'));
    }
}
