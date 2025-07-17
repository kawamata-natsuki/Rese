<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// ===============================
// 認証ルート
// ===============================

// 一般ユーザー
Route::get('/register', [RegisterController::class, 'showRegisterView'])
    ->name('register.view');
Route::post('/register', [RegisterController::class, 'store'])
    ->name('register');

Route::get('/login', [LoginController::class, 'showLoginView'])
    ->name('login.view');
Route::post('/login', [LoginController::class, 'login'])
    ->name('login');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('email')->name('verification.')->middleware('auth')->group(function () {
    Route::get('/verify', [EmailVerificationController::class, 'notice'])
        ->name('notice');
    Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed']) // URL改ざん防止
        ->name('verify');
    Route::post('/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware(['throttle:6,1']) // スパム防止
        ->name('send');
    Route::get('/check', [EmailVerificationController::class, 'check'])
        ->name('check');
});
