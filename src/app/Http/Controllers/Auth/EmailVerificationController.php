<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    // メール認証誘導画面の表示
    public function notice()
    {
        return view('auth.email-verification');
    }

    // メール認証の処理
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect()->route('shop.index');
    }

    // 認証メール再送信
    public function resend(Request $request)
    {
        // 認証済の場合はリダイレクト
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('shop.index');
        }

        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    }

    // メール認証済みか確認してリダイレクト
    public function check()
    {
        $user = auth()->user();

        return optional($user)->hasVerifiedEmail()
            ? redirect()->route('shop.index')
            : redirect()->away('https://mailtrap.io/');
    }
}
