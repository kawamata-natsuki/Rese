<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminShopController extends Controller
{
    // 管理者画面の店舗一覧を表示する
    public function index()
    {
        return view('admin.shops.index');
    }

    // 店舗を作成ページを表示する
    public function create()
    {
        return view('admin.shops.create');
    }

    // 店舗を作成する
    public function store()
    {
        //
    }
}
