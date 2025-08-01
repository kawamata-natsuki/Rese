<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        /** @var \App\Models\User $user */
        $user   = auth()->user();
        $shopId = $request->input('shop_id');

        $isFavorited = $user->favoriteShops()->where('shop_id', $shopId)->exists();

        if ($isFavorited) {
            // いいね済み → 解除
            $user->favoriteShops()->detach($shopId);
        } else {
            // いいね未 → 登録
            $user->favoriteShops()->attach($shopId);
        }

        return back();
    }
}
