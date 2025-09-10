<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminShopOwnerController extends Controller
{
    // 管理者画面のオーナー一覧表示
    public function index()
    {
        return view('admin.shop-owners.index');
    }

    // オーナー作成フォーム表示
    public function create()
    {
        return view('admin.shop-owner.create');
    }

    // オーナー作成
    public function store()
    {
        //
    }
}
