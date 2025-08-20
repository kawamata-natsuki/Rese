<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Shop\CheckinController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Shop\SearchController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\MypageController;
use App\Http\Controllers\User\ReservationController;
use App\Http\Controllers\User\ReviewController;
use Illuminate\Support\Facades\Route;

// ===============================
// 認証ルート
// ===============================

// ユーザー登録
Route::get('/register', [RegisterController::class, 'showRegisterView'])
    ->name('register.view');
Route::post('/register', [RegisterController::class, 'store'])
    ->name('register');

// ログイン・ログアウト
Route::get('/login', [LoginController::class, 'showLoginView'])
    ->name('login.view');
Route::post('/login', [LoginController::class, 'login'])
    ->name('login');
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// メール認証
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

// ===============================
// 一般ユーザー機能
// ===============================

// 飲食店関連ページ（一覧・詳細・検索）
Route::name('shop.')->middleware((['auth', 'verified']))->group(function () {
    Route::get('/', [ShopController::class, 'index'])
        ->name('index');
    Route::get('/shops/{shop}', [ShopController::class, 'show'])
        ->name('show');
    // リアルタイム検索用
    Route::get('shops/search/ajax', [SearchController::class, 'searchAjax'])
        ->name('search.ajax');
});

Route::prefix('user')->name('user.')->middleware(['auth', 'verified'])->group(function () {
    // 予約処理（登録・完了・変更・キャンセル）
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::post('/', [ReservationController::class, 'store'])
            ->name('store');
        Route::get('/done', [ReservationController::class, 'done'])
            ->name('done');
        Route::patch('/{reservation}', [ReservationController::class, 'update'])
            ->name('update');
        Route::delete('/{reservation}', [ReservationController::class, 'destroy'])
            ->name('destroy');
        Route::get('/{reservation}/qr', [ReservationController::class, 'qr'])
            ->name('qr');
    });

    // いいね機能
    Route::post('/favorites', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');

    // レビュー投稿・スキップ
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::post('/{reservation}', [ReviewController::class, 'store'])
            ->name('store');
        Route::post('/{reservation}/skip', [ReviewController::class, 'skip'])
            ->name('skip');
    });

    // マイページ表示
    Route::get('/mypage', [MypageController::class, 'index'])
        ->name('mypage.index');
});

// ===============================
// 店舗代表者ユーザー機能
// ===============================
Route::get('/shop/checkin', [CheckinController::class, 'checkin'])
    ->middleware(['auth', 'can:shop-access'])
    ->name('shop.checkin');
