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
            $query->where('area', $area);
        }

        // 選択されたジャンルと一致する店舗を抽出
        if ($genre && $genre !== 'all') {
            $query->where('genre', $genre);
        }

        // 検索条件に合った店舗を最大10件まで取得
        // 最後の1件は「さらに表示」UI用 
        $shops = $query->limit(11)->get();

        return response()->json($shops);
    }

    // 検索結果ページ表示
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $area    = $request->input('area');
        $genre   = $request->input('genre');

        $query = Shop::query();

        if ($keyword) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        if ($area && $area !== 'all') {
            $query->where('area', $area);
        }

        if ($genre && $genre !== 'all') {
            $query->where('genre', $genre);
        }

        $shops = $query->paginate(20)->withQueryString();

        return view('shop.search', compact(
            'shops',
            'keyword',
            'area',
            'genre'
        ));
    }
}
