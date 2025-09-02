<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Review;
use App\Http\Requests\ReviewRequest;

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
        ]);

        return redirect()->route('user.mypage.index')->with('success', 'レビューを投稿しました');
    }
}
