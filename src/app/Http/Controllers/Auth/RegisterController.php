<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showRegisterView()
    {
        return view('auth.register');
    }

    public function store()
    {
        // ユーザー登録処理
    }

    public function thanks()
    {
        return view('auth.register-thanks');
    }
}
