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
      @if($isPast)
        @if($reservation->reservation_status === \App\Enums\ReservationStatus::VISITED) fas fa-check-circle
        @elseif($reservation->reservation_status === \App\Enums\ReservationStatus::CANCELLED) fas fa-ban
        @elseif($reservation->reservation_status === \App\Enums\ReservationStatus::NO_SHOW) fas fa-exclamation-triangle
        @else fas fa-history
        @endif
      @elseif($reservation->reservation_date->isToday()) fas fa-calendar-day
      @else far fa-clock
      @endif reservation-card__icon"></i>

    <p class="reservation-card__title">
      @if($isPast)
      @php $status = $reservation->reservation_status; @endphp
      <span class="badge badge--muted">{{ $status->label() }}</span>
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

  {{-- レビュー導線：来店済み（VISITED）かつ未レビューのみ表示 --}}
  @if($reservation->reservation_status === \App\Enums\ReservationStatus::VISITED && !$hasReview)
  <div class="reservation-card__actions">
    <a class="reservation-card__button"
      href="{{ route('user.reviews.create', $reservation) }}">
      レビューを書く
    </a>
  </div>
  @endif
</div>