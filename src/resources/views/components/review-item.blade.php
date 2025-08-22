<article class="review">
  <div class="review__header">
    <div class="review__stars">
      @for ($i=1; $i<=5; $i++)
        <span class="star {{ $i <= $review->rating ? 'star--filled' : '' }}">★</span>
        @endfor
    </div>
    <div class="review__meta">
      <span class="review__user">{{ $review->user->name ?? '退会済みユーザー' }}</span>
      <span class="review__date">{{ $review->created_at->format('Y/m/d') }}</span>
    </div>
  </div>
  @if($review->comment)
  <p class="review__comment">{{ $review->comment }}</p>
  @endif
</article>