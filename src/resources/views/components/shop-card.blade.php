@props(['shop'])

<!-- 店舗情報を1件ずつ表示するカード -->
<div class="shop-card">
  <!-- 店舗画像 -->
  <div class="shop-card__image-wrapper">
    <img src="{{ $shop->image_url }}" alt="{{ $shop->name }}の画像" class="shop-card__image">
  </div>

  <!-- 店舗情報 -->
  <div class="shop-card__info-wrapper">

    <!-- 店舗名 -->
    <p class="shop-card__name">{{ $shop->name }}</p>

    <!-- ★レビュー -->
    @php
    $avg = round($shop->reviews_avg_rating ?? 0, 1);
    $count = $shop->reviews_count ?? 0;
    $filled = (int) floor($avg);
    $half = ($avg - $filled) >= 0.5;
    $empty = 5 - $filled - ($half ? 1 : 0);
    @endphp
    <div class="shop-card__rating {{ $count === 0 ? 'is-empty' : '' }}">
      <div class="stars" aria-label="平均{{ number_format($avg,1) }}点、{{ $count }}件のレビュー">
        @if($count > 0)
        @for ($i=0;$i<$filled;$i++) <span class="star star--filled">★</span> @endfor
          @if($half)
          <span class="star star--half">★</span>
          @endif
          @for($i=0;$i<$empty;$i++)
            <span class="star">★</span>
            @endfor
            @else
            @for ($i=0;$i<5;$i++)
              <span class="star">★</span>
              @endfor
              @endif
      </div>

      @if($count > 0)
      <span class="rating-text">
        <span class="rating-value">{{ number_format($avg,1) }}</span>
        <span class="rating-slash">/</span>
        <small class="rating-scale">5</small>
      </span>
      <span class="rating-count">（{{ $count }}件）</span>
      @else
      <span class="rating-empty-text">（0件）</span>
      @endif
    </div>

    <!-- エリア&ジャンル -->
    <div class="shop-card__tags">
      <span class="shop-card__tag is-area">
        # {{ $shop->area->name }}
      </span>
      <span class="shop-card__tag is-genre">
        # {{ $shop->genre->name }}
      </span>
    </div>

    <!-- 詳しくみるボタン & いいね -->
    <div class="shop-card__action-wrapper">
      <a href="{{ route('shop.show', $shop->id) }}" class="shop-card__button">
        詳しくみる
      </a>

      <div class="shop-card__favorite">
        <form action="{{ route('user.favorites.toggle') }}" method="post">
          @csrf
          <input type="hidden" name="shop_id" value="{{ $shop->id }}">
          <button class="favorite-button" type="submit" aria-label="お気に入りを切り替え">
            <i class="fas fa-heart favorite-icon {{ auth()->user()->favoriteShops->contains($shop) ? 'favorite-icon--active' : '' }}"></i>
          </button>
        </form>
      </div>
    </div>

  </div>
</div>