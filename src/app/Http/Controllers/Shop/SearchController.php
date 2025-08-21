<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // リアルタイム検索 + ページング（AJAX）
    public function searchAjax(Request $request)
    {
        $query = Shop::query()
            ->with(['area:id,name', 'genre:id,name'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // フィルタ
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }
        if ($request->area && $request->area !== 'all') {
            $query->where('area_id', $request->area);
        }
        if ($request->genre && $request->genre !== 'all') {
            $query->where('genre_id', $request->genre);
        }

        // ページネーション
        $shops = $query->orderBy('id')
            ->paginate(12)
            ->appends($request->only('keyword', 'area', 'genre'));

        $html = view('components.shop-cards', compact('shops'))->render();

        return response()->json(['html' => $html]);
    }
}
