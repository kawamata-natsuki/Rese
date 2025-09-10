<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopOwner;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.index', [
            'shopOwnerCount'    => ShopOwner::count(),
            'latestShopOwners'      => ShopOwner::with('shops:id,shop_owner_id,name')
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'created_at']),

            'shopCount'     => Shop::count(),
            'latestShops'   => Shop::latest()
                ->take(5)
                ->get(['id', 'name', 'shop_owner_id', 'created_at']),

            'userCount'     => User::count(),
            'newUsersCount' => User::where('created_at', today())->count(),
        ]);
    }
}
