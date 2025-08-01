<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with(['area', 'genre'])->get();
        return view('shop.index', compact('shops'));
    }

    public function show($id)
    {
        $shop = Shop::findOrFail($id);
        return view('shop.show', compact('shop'));
    }
}
