<?php

namespace App\Http\Controllers\Admin;

use app\Http\Controllers\Controller;
use app\Models\Shop;
use app\Models\ShopOwner;
use app\Models\User;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.index', [
            'ownerCount'    => ShopOwner::count(),
            'latestOwners'  => ShopOwner::with('shops:id,shop_owner_id,name')
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'created_at']),

            'shopCount'     => Shop::count(),
            'latestShops'   => Shop::latest()
                ->take(5)
                ->get(['id', 'name', 'shop-owner_id', 'created_at']),

            'userCount'     => User::count(),
            'newUsersCount' => User::where('created_at', today())->count(),
        ]);
    }
}
