<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // リアルタイム検索処理
    public function searchAjax(Request $request)
    {
        $keyword = $request->input('keyword');
        $area    = $request->input('area');
        $genre   = $request->input('genre');

        $query   = Shop::query();

        // 入力されたキーワードと一致する店舗を部分一致検索する
        if ($keyword) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        // 選択されたエリアと一致する店舗を抽出
        if ($area && $area !== 'all') {
            $query->where('area_id', $area);
        }

        // 選択されたジャンルと一致する店舗を抽出
        if ($genre && $genre !== 'all') {
            $query->where('genre_id', $genre);
        }

        $shops = $query->get();

        $html = view('components.shop-cards', compact('shops'))->render();
        return response()->json(['html' => $html]);
    }
}
