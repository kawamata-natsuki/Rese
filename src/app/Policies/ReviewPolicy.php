<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /** 作成: 予約の本人 & 予約は過去 & 未レビュー */
    public function create(User $user, Reservation $reservation): bool
    {
        if ($reservation->user_id !== $user->id) return false;
        if (!$reservation->startsAt()->isPast()) return false;
        if ($reservation->review()->exists()) return false;
        return true;
    }

    /** 更新/削除: 自分のレビューのみ */
    public function update(User $user, Review $review): bool
    {
        return $review->user_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->id;
    }
}
