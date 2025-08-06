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

        @if ($reservations->isEmpty())
        <p class="mypage__reservations-empty">現在、予約はありません。</p>
        @else
        @foreach($reservations as $reservation)
        <div class="mypage__reservations-item">
          <x-reservation-card :reservation="$reservation" />
        </div>
        @endforeach
        @endif

        <!-- 予約変更モーダル -->
        <div id="reservation-edit-modal" class="modal" style="display: none;">
          <div class="modal-content modal-content--edit">
            <p class="modal-title">予約内容を変更しますか？</p>

            <form id="reservation-edit-form" method="post" action="">
              @csrf
              @method('PATCH')
              <input type="hidden" name="shop_id" value="{{ $reservation->shop_id }}">

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
                  @foreach ($timeSlots as $slot)
                  <option value="{{ $slot }}">{{ $slot }}</option>
                  @endforeach
                </select>
              </label>

              <label data-label="人数">
                <select name="number" id="edit-guests" class="reservation-form__input">
                  @foreach ($numberSlots as $num)
                  <option value="{{ $num }}" {{ old('number', $defaultGuests ?? '') == $num ? 'selected' : '' }}>
                    {{ $num }}人
                  </option>
                  @endforeach
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
            <img id="reservation-qr-image" src="" alt="QRコード" style="width: 200px; height: 200px;">
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
  window.addEventListener('DOMContentLoaded', () => {
    // Choices.js 初期化（時間セレクト）
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

    // ------- 予約変更モーダル -------
    const editButtons = document.querySelectorAll('.reservation-edit-button');
    const editModal = document.getElementById('reservation-edit-modal');
    const editForm = document.getElementById('reservation-edit-form');
    const timeSelect = document.getElementById('edit-time-select');
    const guestsSelect = document.getElementById('edit-guests');

    function generateTimeSlots(opening, closing) {
      const slots = [];
      const [startHour, startMin] = opening.split(':').map(Number);
      const [endHour, endMin] = closing.split(':').map(Number);
      const start = new Date();
      start.setHours(startHour, startMin, 0, 0);
      const end = new Date();
      end.setHours(endHour, endMin, 0, 0);

      while (start < end) {
        const hours = String(start.getHours()).padStart(2, '0');
        const minutes = String(start.getMinutes()).padStart(2, '0');
        slots.push(`${hours}:${minutes}`);
        start.setMinutes(start.getMinutes() + 30);
      }
      return slots;
    }

    editButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.dataset.reservationId;
        const date = button.dataset.date;
        const time = button.dataset.time;
        const guests = button.dataset.guests;
        const opening = button.dataset.opening;
        const closing = button.dataset.closing;

        editForm.action = `/user/reservations/${id}`;

        const slots = generateTimeSlots(opening, closing);
        slots.forEach(slot => {
          const option = document.createElement('option');
          option.value = slot;
          option.textContent = slot;
          if (slot === time.slice(0, 5)) option.selected = true;
          timeSelect.appendChild(option);
        });

        // 🔁 選択肢を再初期化
        timeChoices.clearStore();
        timeChoices.setChoices(
          slots.map(s => ({
            value: s,
            label: s,
            selected: s === time.slice(0, 5)
          })),
          'value',
          'label',
          true
        );

        // 人数
        window.numberSlots.forEach(num => {
          const option = document.createElement('option');
          option.value = num;
          option.textContent = `${num}人`;
          if (parseInt(guests) === num) option.selected = true;
          guestsSelect.appendChild(option);
        });

        // 🔁 選択肢を再初期化
        guestsChoices.clearStore();
        guestsChoices.setChoices(
          window.numberSlots.map(num => ({
            value: num,
            label: `${num}人`,
            selected: parseInt(guests) === num
          })),
          'value',
          'label',
          true
        );

        editModal.style.display = 'flex';
      });
    });

    // QRコードモーダル
    const qrButtons = document.querySelectorAll('.reservation-qr-button');
    const qrModal = document.getElementById('reservation-qr-modal');
    const qrImage = document.getElementById('reservation-qr-image');
    qrButtons.forEach(button => {
      button.addEventListener('click', () => {
        qrImage.src = button.dataset.qrImageUrl;
        qrModal.style.display = 'flex';
      });
    });

    // モーダル閉じる
    document.querySelectorAll('.modal-close-button').forEach(btn => {
      btn.addEventListener('click', () => {
        const modal = btn.closest('.modal');
        modal.style.display = 'none';
      });
    });
  });
</script>
@endsection