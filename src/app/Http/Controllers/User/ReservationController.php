<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store()
    {
        // 予約処理
    }

    public function done()
    {
        return view('user.reservations.done');
    }

    public function update()
    {
        // 予約変更処理
    }

    public function destroy()
    {
        // 予約キャンセル処理
    }
}
