@props(['reservation'])

<!-- 予約状況を1件ずつ表示するカード -->
<div class="reservation-card">
  <!-- ヘッダー（クリックで開閉） -->
  <div class="reservation-card__header">
    <i class="far fa-clock reservation-card__icon"></i>
    <p class="reservation-card__title">
      予約 {{ $reservation->display_number }}
    </p>
    <!-- ヘッダーの右端にQRアイコン -->
    @if ($reservation->reservation_date->isToday())
    <i class="fas fa-qrcode reservation-card__qr-icon"
      title="QRコードを表示"
      data-qr="{{ route('user.reservations.qr', $reservation->id) }}"
      onclick="showQrModal(this)"></i>
    @endif
  </div>

  <!-- コンテンツ（最初は非表示） -->
  <div class="reservation-card__content">
    <p class="reservation-card__item">
      店舗：{{ $reservation->shop->name }}
    </p>
    <p class="reservation-card__item">
      日付：{{ $reservation->reservation_date->format('Y年n月j日') }}
      ({{ ['日', '月', '火', '水', '木', '金', '土'][$reservation->reservation_date->dayOfWeek] }})
    </p>
    <p class="reservation-card__item">
      時間：{{ $reservation->reservation_time->format('H:i') }}
    </p>
    <p class="reservation-card__item">
      人数：{{ $reservation->number_of_guests }}名
    </p>
  </div>

  <!-- 予約変更・キャンセルボタン -->
  <div class="reservation-card__actions">
    <button type="button"
      class="reservation-edit-button reservation-card__button"
      data-reservation-id="{{ $reservation->id }}"
      data-shop="{{ $reservation->shop->name }}"
      data-date="{{ $reservation->reservation_date->format('Y-m-d') }}"
      data-time="{{ $reservation->reservation_time->format('H:i') }}"
      data-guests="{{ $reservation->number_of_guests }}"
      data-opening="{{ $reservation->shop->opening_time->format('H:i') }}"
      data-closing="{{ $reservation->shop->closing_time->format('H:i') }}">
      変更
    </button>

    <button
      type="button"
      class="reservation-cancel-button reservation-card__button"
      data-reservation-id="{{ $reservation->id }}">
      キャンセル
    </button>
  </div>
</div>