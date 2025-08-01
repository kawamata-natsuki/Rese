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

        $favorite = $user->favorites()->where('shop_id', $shopId);

        if ($favorite->existst()) {
            // いいね済み → 解除
            $favorite->delete();
        } else {
            // いいね未 → 登録
            $user->favorites()->create(['shop_id' => $shopId]);
        }
    }
}
