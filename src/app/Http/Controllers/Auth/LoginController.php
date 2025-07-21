<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginView()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // バリデーション（あとでFormRequest作成）
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // ログイン試行
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('shop.index');
        }

        // 失敗した場合
        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
