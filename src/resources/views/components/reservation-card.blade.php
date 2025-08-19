@props(['reservation'])

@php
$startsAt = \Carbon\Carbon::parse(
$reservation->reservation_date->format('Y-m-d').' '.$reservation->reservation_time->format('H:i:s')
);
$isPast = $startsAt->isPast();
@endphp

<!-- 予約状況を1件ずつ表示するカード -->
<div class="reservation-card">
  <div class="reservation-card__header">
    <i class="far fa-clock reservation-card__icon"></i>
    <p class="reservation-card__title">
      @if($isPast)
      <span class="badge badge--muted">完了</span>
      @else
      予約 {{ $reservation->display_number }}
      @if($reservation->reservation_date->isToday())
      <span class="badge">本日</span>
      @endif
      @endif
    </p>

    @if ($reservation->reservation_date->isToday() && !$isPast)
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
  @unless($isPast || $readonly)
  <div class="reservation-card__actions">
    {{-- 変更／キャンセル ボタンは未来のみ --}}
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

    <button type="button"
      class="reservation-cancel-button reservation-card__button"
      data-reservation-id="{{ $reservation->id }}">
      キャンセル
    </button>
  </div>
  @endunless
</div>