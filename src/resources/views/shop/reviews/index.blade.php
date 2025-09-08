@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/shop/reviews/index.css') }}">
@endsection

@section('title')
{{ $shop->name }}のレビュー
@endsection

@section('content')
<div class="shop-reviews-page">
  <div class="shop-reviews-page__container">

    {{-- Header --}}
    <div class="shop-review-header">
      <img class="shop-review-header__thumb" src="{{ $shop->image_url }}" alt="{{ $shop->name }}">
      <div class="shop-review-header__meta">
        <h1 class="shop-review-header__name">{{ $shop->name }}のレビュー</h1>

        <div class="shop-review-header__tags">
          <span># {{ $shop->area->name }}</span>
          <span># {{ $shop->genre->name }}</span>
        </div>

        {{-- 平均評価＋件数 --}}
        @php
        $avg = $avgRating;
        $count = $reviewsCount;
        $filled = floor($avg);
        $half = ($avg - $filled) >= 0.5;
        @endphp
        <div class="rating-summary">
          <div class="rating-summary__stars">
            @for($i=1;$i<=5;$i++)
              <span class="star {{ $i <= $filled ? 'star--filled' : ($i==$filled+1 && $half ? 'star--half' : '') }}">★</span>
              @endfor
          </div>
          <div class="rating-summary__text">
            <strong>{{ number_format($avg,1) }}</strong><span>/5</span>
            <span class="count">（{{ $count }}件）</span>
          </div>

          <div class="shop-review-header__actions">
            <a class="btn--ghost" href="{{ route('shop.show', $shop) }}">店舗詳細へ</a>
            <a class="btn--primary" href="{{ route('shop.show', $shop) }}#reserve">予約する</a>
          </div>
        </div>

        {{-- ★分布（ヘッダー内に収めて右余白を解消） --}}
        @if($reviewsCount > 0)
        <div class="rating-dist rating-dist--header">
          @foreach($distribution as $stars => $pct)
          <div class="rating-dist__row">
            <span class="rating-dist__label">{{ $stars }}★</span>
            <div class="rating-dist__bar"><span style="width: {{ $pct }}%"></span></div>
            <span class="rating-dist__pct">{{ $pct }}%（{{ $counts[$stars] ?? 0 }}件）</span>
          </div>
          @endforeach
        </div>
        @endif
      </div>
    </div>

    <div class="shop-review-body">

      {{-- 並び替え（分布の下・右寄せ） --}}
      <div class="review-sortbar">
        <form method="GET">
          <label class="sort-label" for="sort">並び替え：</label>
          <select id="sort" name="sort" class="sort-select" onchange="this.form.submit()">
            <option value="recent" @selected($sort==='recent' )>新しい順</option>
            <option value="rating" @selected($sort==='rating' )>評価が高い順</option>
            <option value="lowrate" @selected($sort==='lowrate' )>評価が低い順</option>
          </select>
        </form>
      </div>

      {{-- レビュー一覧 --}}
      @if($reviews->count())
      <ul class="review-list">
        @foreach($reviews as $review)
        <li class="review-card">
          <div class="review-card__header">
            <div class="review-card__stars">
              @php $r = (int) $review->rating; @endphp
              @for($i=1; $i<=5; $i++)
                <span class="star {{ $i <= $r ? 'star--filled' : '' }}">★</span>
                @endfor
                <span class="review-card__rating-num">{{ $review->rating }} / 5</span>
            </div>
            <time class="review-card__date" datetime="{{ $review->created_at->toDateString() }}">
              {{ $review->created_at->format('Y/m/d') }}
            </time>
          </div>

          <h3 class="review-card__title">{{ $review->title }}</h3>

          <p class="review-card__comment">
            {{ Str::limit($review->comment, 240) }}
          </p>

          <div class="review-card__footer">
            <div class="review-card__user">
              <i class="fas fa-user-circle"></i>
              <span class="review-card__name">
                {{ $review->display_name ?? \App\Support\DisplayName::mask(optional($review->user)->name ?? '') }}
              </span>
            </div>
            <button class="btn-helpful" data-review-id="{{ $review->id }}" type="button" disabled>
              <i class="far fa-thumbs-up"></i> 役に立った
            </button>
          </div>
        </li>
        @endforeach
      </ul>

      <div class="pagination">
        {{ $reviews->withQueryString()->links() }}
      </div>
      @else
      <div class="review-empty">
        <p>まだレビューがありません。</p>
        <a class="btn--primary" href="{{ route('shop.show', $shop) }}#reserve">このお店を予約する</a>
      </div>
      @endif

    </div>
  </div>
</div>
@endsection