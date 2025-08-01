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
          <button class="favorite-button" type="submit">
            <i class="fas fa-heart favorite-icon {{ auth()->user()->favoriteShops->contains($shop) ? 'favorite-icon--active' : '' }}"></i>
          </button>
        </form>
      </div>
    </div>

  </div>
</div>