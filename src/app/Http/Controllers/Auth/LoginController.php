<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    // ログイン画面表示
    public function showLoginView()
    {
        return view('auth.login');
    }

    // ログイン処理
    public function login(LoginRequest $request)
    {
        // ログイン試行回数制限をチェック
        $this->checkRateLimit($request);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // ログイン試行
        if (Auth::attempt($credentials, $remember)) {
            // ログイン成功したらレート制限リセット
            RateLimiter::clear($this->throttlekey($request));
            // セッションID再生成
            $request->session()->regenerate();
            return redirect()->route('shop.index')
                ->with('success', 'ログインしました！');
        }

        // 失敗した場合
        RateLimiter::hit($this->throttleKey($request), 60);
        throw ValidationException::withMessages([
            'login' => 'ログイン情報が登録されていません',
        ]);
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        Auth::logout();

        // セッションを無効化してCSRFトークンを再生成
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後のリダイレクト先
        return redirect()->route('login');
    }

    // =========================
    // RateLimiter 関連
    // =========================

    // ログインの試行回数を制限する処理
    protected function checkRateLimit(Request $request)
    {
        // 1分間に5回以上のログイン試行があった場合、制限をかける
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        // ログイン制限に達している場合は、再試行可能になるまでの残り秒数を取得し、エラーメッセージを表示
        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        throw ValidationException::withMessages([
            'login' => "ログイン試行が多すぎます。{$seconds}秒後に再度お試しください。",
        ]);
    }

    // レート制限のキー(email+IP)を生成する
    protected function throttleKey(Request $request): string
    {
        // email + IPアドレス の組み合わせで試行回数をカウント
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}
