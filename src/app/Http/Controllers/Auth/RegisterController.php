<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    // 会員登録画面表示
    public function showRegisterView()
    {
        return view('auth.register');
    }

    // 会員登録の処理
    public function store(RegisterRequest $request, CreateNewUser $creator)
    {
        // ユーザー作成・メール認証のメール自動送信
        event(new Registered($user = $creator->create($request->validated())));

        Auth::login($user);

        // メール認証画面へリダイレクト
        return redirect()->route('verification.notice');
    }
}
