@extends('layouts.app')

@section('css')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="{{ asset('css/components/reservation-card.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/shop-card.css') }}">
<link rel="stylesheet" href="{{ asset('css/user/mypage/index.css') }}">
@endsection

@section('title', 'マイページ')


@section('content')
<div class="mypage">
  <div class="mypage__container">

    <div class="mypage__header">
      <p class="mypage__header-name">
        {{ $user->name }} さん
      </p>
    </div>

    <div class="mypage__body">
      <!-- 予約一覧 -->
      <div class="mypage__reservations">
        <span class="mypage__reservations-heading">
          予約状況
        </span>

        {{-- 今後の予約 --}}
        @if ($upcomingReservations->isEmpty())
        <div class="mypage__reservations-item">
          <div class="reservation-card reservation-card--empty">
            <div class="reservation-card__header reservation-card__header--empty">
              <i class="far fa-calendar-minus reservation-card__icon"></i>
              <p class="reservation-card__title">予約はありません</p>
            </div>
            <div class="reservation-card__content">
              <p class="reservation-card__item">新しい予約をしてみましょう！</p>
              <a href="{{ route('shop.index') }}" class="reservation-card__cta">店舗一覧へ</a>
            </div>
          </div>
        </div>
        @else
        @foreach ($upcomingReservations as $reservation)
        <div class="mypage__reservations-item">
          <x-reservation-card :reservation="$reservation" />
        </div>
        @endforeach
        @endif

        {{-- 過去の予約（折りたたみ） --}}
        @if ($pastReservations->isNotEmpty())
        <details id="past-reservations" class="collapsible" {{ $upcomingReservations->isEmpty() ? 'open' : '' }}>
          <summary class="collapsible__summary" aria-label="過去の予約を開閉">
            <span class="collapsible__title">過去の予約</span>
            <span class="collapsible__count">{{ $pastReservations->count() }}件</span>
            <i class="fas fa-chevron-down collapsible__chevron" aria-hidden="true"></i>
          </summary>

          <div class="collapsible__content">
            @foreach($pastReservations as $reservation)
            <div class="mypage__reservations-item">
              <x-reservation-card :reservation="$reservation" :readonly="true" />
            </div>
            @endforeach
          </div>
        </details>
        @endif

        <!-- 予約変更モーダル -->
        <div id="reservation-edit-modal" class="modal" style="display: none;">
          <div class="modal-content modal-content--edit">
            <p class="modal-title">予約内容を変更しますか？</p>

            <form id="reservation-edit-form" method="post" action="">
              @csrf
              @method('PATCH')
              <input type="hidden" name="shop_id" id="edit-shop-id" value="">

              <label data-label="日付">
                <select name="date" id="edit-date-select" class="reservation-form__input">
                  @foreach ($dateSlots as $slot)
                  <option value="{{ $slot['value'] }}" {{ old('date', $selectedDate ?? '') === $slot['value'] ? 'selected' : '' }}>
                    {{ $slot['label'] }}
                  </option>
                  @endforeach
                </select> </label>

              <label data-label="時間">
                <select name="time" id="edit-time-select" class="reservation-form__input">
                </select>
              </label>

              <label data-label="人数">
                <select name="number" id="edit-guests" class="reservation-form__input">
                </select>
              </label>

              <div class="modal-buttons">
                <button type="submit">変更する</button>
                <button type="button" class="modal-close-button">キャンセル</button>
              </div>
            </form>
          </div>
        </div>

        <!-- キャンセル確認モーダル -->
        <div id="reservation-cancel-modal" class="modal" style="display: none;">
          <div class="modal-content">
            <p>この予約をキャンセルしてもよろしいですか？</p>
            <form id="reservation-cancel-form" method="post" action="">
              @csrf
              @method('DELETE')
              <button type="submit">はい</button>
              <button type="button" class="modal-close-button">いいえ</button>
            </form>
          </div>
        </div>

        <!-- QRコード表示モーダル -->
        <div id="reservation-qr-modal" class="modal" style="display: none;">
          <div class="modal-content">
            <p>QRコードを提示してください</p>
            <img id="reservation-qr-image" src="/user/reservations/8/qr" alt="QRコード" style="width: 200px; height: 200px;">
            <button type="button" class="modal-close-button">閉じる</button>
          </div>
        </div>
      </div>

      <!-- お気に入り一覧 -->
      <div class="mypage__favorites">
        <span class="mypage__favorites-heading">
          お気に入り店舗
        </span>

        <div class="mypage__favorites-grid">
          @foreach($favoriteShops as $shop)
          <div class="mypage__favorites-item">
            @include('components.shop-card', ['shop' => $shop])
          </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

<script>
  window.numberSlots = @json($numberSlots);
</script>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // ------- Choices 初期化 -------
    const dateChoices = new Choices('#edit-date-select', {
      searchEnabled: false,
      itemSelectText: '',
      shouldSort: false,
      position: 'bottom'
    });
    const timeChoices = new Choices('#edit-time-select', {
      searchEnabled: false,
      itemSelectText: '',
      shouldSort: false,
      position: 'bottom'
    });
    const guestsChoices = new Choices('#edit-guests', {
      searchEnabled: false,
      itemSelectText: '',
      shouldSort: false,
      position: 'bottom'
    });

    // ------- 予約変更モーダル -------
    const editButtons = document.querySelectorAll('.reservation-edit-button');
    const editModal = document.getElementById('reservation-edit-modal');
    const editForm = document.getElementById('reservation-edit-form');
    const editShopIdInp = document.querySelector('#reservation-edit-form input[name="shop_id"]');

    function generateTimeSlots(opening, closing) {
      const slots = [];
      const [sh, sm] = opening.split(':').map(Number);
      const [eh, em] = closing.split(':').map(Number);
      const start = new Date();
      start.setHours(sh, sm, 0, 0);
      const end = new Date();
      end.setHours(eh, em, 0, 0);
      while (start < end) {
        const hh = String(start.getHours()).padStart(2, '0');
        const mm = String(start.getMinutes()).padStart(2, '0');
        slots.push(`${hh}:${mm}`);
        start.setMinutes(start.getMinutes() + 30);
      }
      return slots;
    }

    editButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.dataset.reservationId;
        const date = button.dataset.date; // "YYYY-MM-DD"
        const time = (button.dataset.time || '').slice(0, 5); // "HH:mm"
        const guests = parseInt(button.dataset.guests, 10);
        const opening = button.dataset.opening; // "HH:mm"
        const closing = button.dataset.closing; // "HH:mm"
        const shopId = button.dataset.shopId || '';

        // フォームの送信先＆hidden値
        editForm.action = `/user/reservations/${id}`;
        if (editShopIdInp) editShopIdInp.value = shopId;

        // 時間スロットを再構築（重複追加なし）
        const slots = generateTimeSlots(opening, closing);
        timeChoices.clearStore();
        timeChoices.setChoices(
          slots.map(s => ({
            value: s,
            label: s,
            selected: s === time
          })),
          'value', 'label', true
        );

        // 人数スロットを再構築（重複追加なし）
        guestsChoices.clearStore();
        guestsChoices.setChoices(
          (window.numberSlots || []).map(n => ({
            value: String(n),
            label: `${n}人`,
            selected: n === guests
          })),
          'value', 'label', true
        );

        // 日付は既存候補から選択状態だけ更新
        if (date) dateChoices.setChoiceByValue(date);

        editModal.style.display = 'flex';
      });
    });

    // ------- キャンセルモーダル -------
    const cancelButtons = document.querySelectorAll('.reservation-cancel-button');
    const cancelModal = document.getElementById('reservation-cancel-modal');
    const cancelForm = document.getElementById('reservation-cancel-form');

    cancelButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.dataset.reservationId;
        cancelForm.action = `/user/reservations/${id}`;
        cancelModal.style.display = 'flex';
      });
    });

    // ------- QRコードモーダル（.reservation-qr-button がある場合） -------
    const qrButtons = document.querySelectorAll('.reservation-qr-button');
    const qrModal = document.getElementById('reservation-qr-modal');
    const qrImage = document.getElementById('reservation-qr-image');
    qrButtons.forEach(button => {
      button.addEventListener('click', () => {
        const url = button.dataset.qr || button.dataset.qrImageUrl;
        if (url) qrImage.src = url;
        qrModal.style.display = 'flex';
      });
    });

    // ------- モーダル閉じる -------
    document.querySelectorAll('.modal-close-button').forEach(btn => {
      btn.addEventListener('click', () => {
        const modal = btn.closest('.modal');
        modal.style.display = 'none';
      });
    });
  });
</script>
@endsection