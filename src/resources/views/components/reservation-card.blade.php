@props(['reservation'])

<!-- 予約状況を1件ずつ表示するカード -->
<div class="reservation-card">
  <!-- ヘッダー（クリックで開閉） -->
  <div class="reservation-card__header">
    <i class="far fa-clock reservation-card__icon"></i>
    <p class="reservation-card__title">
      予約 {{ $reservation->display_number }}
    </p>
  </div>

  <!-- コンテンツ（最初は非表示） -->
  <div class="reservation-card__content">
    <p class="reservation-card__item">
      店舗：{{ $reservation->shop->name }}
    </p>
    <p class="reservation-card__item">
      日付：{{ $reservation->reservation_date->format('Y年n月j日') }}
    </p>
    <p class="reservation-card__item">
      時間：{{ $reservation->reservation_time->format('H:i') }}
    </p>
    <p class="reservation-card__item">
      人数：{{ $reservation->number_of_guests }}名
    </p>

    <!-- 今後予約変更実装予定 -->
    <!-- 予約ステータス表示予定 -->
  </div>
</div>