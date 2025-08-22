<div class="shop-review-header">
  <img class="shop-review-header__thumb" src="{{ $shop->image_url }}" alt="{{ $shop->name }}">
  <div class="shop-review-header__meta">
    <h1 class="shop-review-header__name">{{ $shop->name }} のレビュー</h1>
    <div class="shop-review-header__tags">
      <span># {{ $shop->area->name }}</span>
      <span># {{ $shop->genre->name }}</span>
    </div>

    {{-- 平均と件数（カードと同じ見た目でOK） --}}
    <div class="rating-summary">
      @php
      $avg = $avgRating; $count = $reviewsCount;
      $filled = (int) floor($avg);
      $half = ($avg - $filled) >= 0.5;
      $empty = 5 - $filled - ($half ? 1 : 0);
      @endphp
      <div class="rating-summary__stars">
        @if($count > 0)
        @for($i=0;$i<$filled;$i++) <span class="star star--filled">★</span> @endfor
          @if($half) <span class="star star--half">★</span> @endif
          @for($i=0;$i<$empty;$i++) <span class="star">★</span> @endfor
            @else
            @for($i=0;$i<5;$i++) <span class="star">★</span> @endfor
              @endif
      </div>
      <div class="rating-summary__text">
        @if($count > 0)
        <strong>{{ number_format($avg,1) }}</strong><span class="rating-slash">/</span><small class="rating-scale">5</small>
        <span class="count">（{{ $count }}件）</span>
        @else
        <span class="count">（0件）</span>
        @endif
      </div>

      <div class="shop-review-header__actions">
        <a class="btn--ghost" href="{{ route('shop.show', $shop) }}">店舗詳細へ</a>
        <a class="btn--primary" href="{{ route('shop.show', $shop) }}#reserve">予約する</a>
      </div>
    </div>
  </div>
</div>