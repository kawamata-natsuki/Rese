@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/components/shop-card.css') }}">
<link rel="stylesheet" href="{{ asset('css/shop/show.css') }}">
@endsection

@section('title')
{{ $shop->name }}
@endsection

@section('content')
<div class="shop-show-page">
  <div class="shop-show-page__container">
    <!-- 店舗名＋前のページに戻るボタン -->
    <div class="shop-show-page__title-bar">
      <a href="{{ $backUrl }}" class="shop-show-page__button--back" aria-label="前のページに戻る">
        <i class="fas fa-chevron-left"></i>
      </a>
      <p class="shop-show-page__name">{{ $shop->name }}</p>
    </div>

    <!-- 左：店舗情報　右：予約フォーム -->
    <div class="shop-show-page__body">
      <!-- 左側：店舗情報 -->
      <div class="shop-show-page__info-wrapper">

        <!-- 店舗画像 -->
        <div class="shop-show-page__image-wrapper">
          <img src="{{ $shop->image_url }}" alt="{{ $shop->name }}の画像" class="shop-show-page__image">
        </div>

        <!-- エリア＋ジャンル -->
        <div class="shop-show-page__tags">
          <span class="shop-show-page__tag is-area">
            # {{ $shop->area->name }}
          </span>
          <span class="shop-show-page__tag is-genre">
            # {{ $shop->genre->name }}
          </span>
        </div>

        <!-- ★レビュー -->
        <div class="rating-summary">
          @php
          $avg = round($shop->reviews()->avg('rating') ?? 0, 1);
          $count = $shop->reviews()->count();
          $filled = (int) floor($avg);
          $half = ($avg - $filled) >= 0.5;
          $empty = 5 - $filled - ($half ? 1 : 0);
          @endphp

          <div class="rating-summary__stars">
            @if($count > 0)
            @for ($i=0; $i<$filled; $i++)
              <span class="star star--filled">★</span>
              @endfor
              @if($half)
              <span class="star star--half">★</span>
              @endif
              @for ($i=0; $i<$empty; $i++)
                <span class="star">★</span>
                @endfor
                @else
                @for ($i=0; $i<5; $i++)
                  <span class="star">★</span>
                  @endfor
                  @endif
          </div>

          <div class="rating-summary__text">
            @if($count > 0)
            <strong>{{ number_format($avg,1) }}</strong>
            <span class="rating-slash">/</span>
            <small class="rating-scale">5</small>
            <span class="count">（{{ $count }}件）</span>
            @else
            <span class="count">（0件）</span>
            @endif
          </div>
        </div>

        <!-- 店舗紹介 -->
        <p class="shop-show-page__description">
          {{ $shop->description }}
        </p>
      </div>

      <!-- 右側：予約フォーマット -->
      <div class="shop-show-page__reservation">
        <form action="{{ route('user.reservations.store') }}" method="post" class="reservation-form">
          @csrf
          <!-- 予約店舗のID -->
          <input type="hidden" name="shop_id" value="{{ $shop->id }}">

          <h1 class="reservation-form__heading">予約</h1>

          <div class="reservation-form__fields">
            <!-- 日にち選択 -->
            <div class="reservation-form__field" onclick="flatpickrInstance.open()">
              <label for="date" class="reservation-form__label">
                <i class="fas fa-calendar-day reservation-form__icon"></i>
              </label>
              <div class="reservation-form__input-block">
                <input
                  type="text"
                  name="date"
                  id="date"
                  value="{{ old('date', '') }}"
                  class="reservation-form__input no-default-calendar"
                  placeholder="日付を選択してください">
                <x-error-message field="date" />
              </div>
            </div>

            <!-- 時間選択 -->
            <div class="reservation-form__field">
              <label for="time" class="reservation-form__label">
                <i class="far fa-clock reservation-form__icon"></i>
              </label>
              <div class="reservation-form__input-block">
                <select name="time" id="time"
                  class="reservation-form__input {{ old('time') ? '' : 'is-placeholder' }}">
                  <option value="" hidden {{ old('time') ? '' : 'selected' }} class="is-placeholder">
                    時刻を選択してください
                  </option>
                  @foreach ($timeSlots as $slot)
                  <option value="{{ $slot }}" {{ old('time') === $slot ? 'selected' : '' }}>
                    {{ $slot }}
                  </option>
                  @endforeach
                </select>
                <x-error-message field="time" class="error-message--offset" />
              </div>
            </div>

            <!-- 人数選択 -->
            <div class="reservation-form__field">
              <label for="number" class="reservation-form__label">
                <i class="fas fa-user reservation-form__icon"></i>
              </label>
              <div class="reservation-form__input-block">
                <select name="number" id="number"
                  class="reservation-form__input {{ old('number') ? '' : 'is-placeholder' }}">
                  <option value="" hidden {{ old('number') ? '' : 'selected' }} class="is-placeholder">
                    人数を選択してください
                  </option>
                  @foreach ($numberSlots as $num)
                  <option value="{{ $num }}" {{ old('number') == $num ? 'selected' : '' }}>
                    {{ $num }}人
                  </option>
                  @endforeach
                </select>
                <x-error-message field="number" class="error-message--offset" />
              </div>
            </div>
          </div>

          <ul class="reservation-form__notes">
            <li>キャンセルは前日までにご連絡ください。</li>
            <li>アレルギーがある方は備考欄でお知らせください。</li>
          </ul>

          <!-- 予約するボタン -->
          <div class="reservation-form__button-wrapper">
            <button class="reservation-form__button">予約する</button>
          </div>
        </form>
      </div>
    </div>

    <section class="shop-show-page__reviews">
      <h2 class="shop-show-page__reviews-title">最新のレビュー</h2>

      @forelse ($recentReviews as $review)
      @include('components.review-item', ['review' => $review])
      @empty
      <p class="reviews__empty">まだレビューはありません。</p>
      @endforelse

      @if ($reviewsCount > 3)
      <a class="reviews__more" href="{{ route('shops.reviews.index', $shop) }}">
        もっと見る（{{ $reviewsCount - 3 }}件）
      </a>
      @endif
    </section>

  </div>
</div>
@endsection

<script>
  document.addEventListener('DOMContentLoaded', function() {
    flatpickrInstance = flatpickr("#date", {
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "Y年n月j日（D）", // 表示用フォーマット
      locale: flatpickr.l10ns.ja,
      defaultDate: @json(old('date') ?? null),
      disableMobile: true
    });

    // 👇 セレクトボックスにプレースホルダー色を適用する共通関数
    const applySelectColorHandler = (selectId) => {
      const select = document.getElementById(selectId);
      if (!select) return;

      const updateSelectColor = () => {
        const isPlaceholder = !select.value;
        select.style.color = isPlaceholder ? '#999' : '#000';

        if (!isPlaceholder) {
          select.classList.remove('is-placeholder');
        }
      };

      setTimeout(updateSelectColor, 0); // 初期化
      select.addEventListener("change", updateSelectColor);
    };

    // ⬇️ 時刻と人数に適用！
    applySelectColorHandler('time');
    applySelectColorHandler('number');
  });

  document.addEventListener('DOMContentLoaded', () => {
    const $ = (s) => document.querySelector(s);
    const dateI = $('#date'),
      timeI = $('#time'),
      numI = $('#number');
    const sumDate = $('#sum-date'),
      sumTime = $('#sum-time'),
      sumNum = $('#sum-number');

    const fmt = (v, empty = '未選択') => (v && v.trim()) ? v : empty;

    function updateSummary() {
      // flatpickr の altInput を使ってるなら表示用は altInput.value
      const d = dateI?.nextElementSibling?.value || dateI.value;
      sumDate.textContent = fmt(d);
      sumTime.textContent = fmt(timeI.value);
      sumNum.textContent = fmt(numI.value ? `${numI.value}人` : '');
    }

    ['change', 'input'].forEach(ev => {
      dateI?.addEventListener(ev, updateSummary);
      timeI?.addEventListener(ev, updateSummary);
      numI?.addEventListener(ev, updateSummary);
    });

    updateSummary();
  });
</script>