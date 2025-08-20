@props(['reservation','readonly' => false])

@php
$startsAt = \Carbon\Carbon::parse(
$reservation->reservation_date->format('Y-m-d').' '.$reservation->reservation_time->format('H:i:s')
);
$isPast = $startsAt->isPast();
// eager load 済みなら relation 存在で十分（N+1回避）
$hasReview = !is_null($reservation->review);
@endphp

<div class="reservation-card
  @if($isPast) reservation-card--past
  @elseif($reservation->reservation_date->isToday()) reservation-card--today
  @else reservation-card--upcoming
  @endif">
  <div class="reservation-card__header">
    <i class="
      @if($isPast) fas fa-history
      @elseif($reservation->reservation_date->isToday()) fas fa-calendar-day
      @else far fa-clock
      @endif reservation-card__icon"></i>

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

  <div class="reservation-card__content">
    <p class="reservation-card__item">店舗：{{ $reservation->shop->name }}</p>
    <p class="reservation-card__item">
      日付：{{ $reservation->reservation_date->format('Y年n月j日') }}
      ({{ ['日','月','火','水','木','金','土'][$reservation->reservation_date->dayOfWeek] }})
    </p>
    <p class="reservation-card__item">時間：{{ $reservation->reservation_time->format('H:i') }}</p>
    <p class="reservation-card__item">人数：{{ $reservation->number_of_guests }}名</p>
  </div>

  {{-- 未来：変更/キャンセル（readonlyの時は出さない） --}}
  @if(!$isPast && !$readonly)
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

    <button type="button"
      class="reservation-cancel-button reservation-card__button"
      data-reservation-id="{{ $reservation->id }}">
      キャンセル
    </button>
  </div>
  @endif

  {{-- 過去：レビュー導線（レビュー未投稿のときだけ表示） --}}
  @if($isPast && !$hasReview)
  <div class="reservation-card__actions">
    <a class="reservation-card__button"
      href="{{ route('user.reviews.create', $reservation) }}">
      レビューを書く
    </a>
  </div>
  @endif
</div>