<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Review;
use App\Http\Requests\ReviewRequest;
use App\Support\DisplayName;
use App\Models\Admin;
use App\Notifications\Admin\LowRatingReview;

class ReviewController extends Controller
{
    public function index()
    {
        return view('user.reviews.index');
    }

    public function create(Reservation $reservation)
    {
        $this->authorize('create', [Review::class, $reservation]);
        return view('user.reviews.create', compact('reservation'));
    }

    public function store(ReviewRequest $request, Reservation $reservation)
    {
        $this->authorize('create', [Review::class, $reservation]);

        $data = $request->validated();

        // 二重投稿のガード
        if ($reservation->review()->exists()) {
            return redirect()
                ->route('user.mypage.index')
                ->with('info', 'この予約はすでにレビュー済みです。');
        }

        Review::create([
            'user_id'        => $request->user()->id,
            'shop_id'        => $reservation->shop_id,
            'reservation_id' => $reservation->id,
            'title'          => $data['title'],
            'rating'         => $data['rating'],
            'comment'        => $data['comment'] ?? null,
            'display_name'   => DisplayName::mask($request->user()->name ?? ''),
        ]);

        // 管理者に低評価レビューを通知（しきい値: ★2以下）
        if ((int) $data['rating'] <= 2) {
            Admin::query()->each(function (Admin $admin) use ($reservation) {
                $review = $reservation->review()->latest('id')->first();
                if ($review) {
                    $admin->notify(new LowRatingReview($review));
                }
            });
        }

        return redirect()
            ->route('user.mypage.index')
            ->with('success', 'レビューを投稿しました');
    }
}
