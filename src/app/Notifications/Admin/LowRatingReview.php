<?php

namespace App\Notifications\Admin;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LowRatingReview extends Notification implements ShouldQueue
{
  use Queueable;

  public function __construct(private readonly Review $review) {}

  public function via(object $notifiable): array
  {
    return ['database'];
  }

  public function toDatabase(object $notifiable): array
  {
    $shopName = optional($this->review->shop)->name ?? '対象店舗';
    $title = '低評価レビューが投稿されました';
    $message = sprintf('%s に★%dのレビューが投稿されました', $shopName, (int) $this->review->rating);

    $url = route('shop.reviews.index', ['shop' => $this->review->shop_id], false);

    return [
      'type'       => 'low_rating_review',
      'title'      => $title,
      'message'    => $message,
      'url'        => $url,
      'shop_id'    => $this->review->shop_id,
      'review_id'  => $this->review->id,
      'rating'     => (int) $this->review->rating,
    ];
  }
}
