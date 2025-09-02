<article class="review">
  <div class="review__header">
    <div class="review__stars">
      @for ($i=1; $i<=5; $i++)
        <span class="star {{ $i <= $review->rating ? 'star--filled' : '' }}">★</span>
        @endfor
    </div>
    <div class="review__meta">
      <span class="review__date">{{ $review->created_at->format('Y/m/d') }}</span>
    </div>
  </div>
  <h4 class="review-item__title">{{ $review->title }}</h4>
  @if($review->comment)
  <p class="review__comment">{{ $review->comment }}</p>
  @endif
</article>