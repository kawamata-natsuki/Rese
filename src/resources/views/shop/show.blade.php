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
          <!-- 予約するボタン -->
          <div class="reservation-form__button-wrapper">
            <button class="reservation-form__button">予約する</button>
          </div>
        </form>
      </div>
    </div>
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
</script>