<?php

namespace App\Http\Controllers\User;

use App\Models\Shop;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ユーザーの予約とお気に入りを取得
        $reservations = $user->reservations()->with('shop.area', 'shop.genre')->get();
        $favoriteShops = $user->favoriteShops;

        return view('user.mypage.index', [
            'user' => $user,
            'reservations'  => $reservations,
            'favoriteShops' => $favoriteShops,
        ]);
    }
}
