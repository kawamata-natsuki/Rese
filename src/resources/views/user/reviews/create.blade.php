@extends('layouts.app')

@section('css')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<link rel="stylesheet" href="{{ asset('css/user/reviews/create.css') }}">
@endsection

@section('title', 'レビュー投稿')

@section('content')
<div class="review-wrap">
  <a href="{{ route('user.mypage.index') }}" class="btn-back">
    <i class="fas fa-chevron-left" aria-hidden="true"></i>
    <span class="btn-back__text">マイページに戻る</span>
  </a>

  <div class="review-card">
    <div class="card-header">レビュー投稿</div>
    <div class="card-body">

      {{-- 予約サマリ（画像16:9＋情報） --}}
      <div class="summary-block">
        <div class="summary-media">
          <img src="{{ $reservation->shop->image_url }}" alt="{{ $reservation->shop->name }}">
        </div>
        <div class="summary-info">
          <p class="summary__title">{{ $reservation->shop->name }}</p>
          <p class="summary__row">
            <i class="far fa-calendar"></i>
            {{ $reservation->reservation_date->format('Y年n月j日（'.['日','月','火','水','木','金','土'][$reservation->reservation_date->dayOfWeek].'）') }}
            {{ $reservation->reservation_time->format('H:i') }}
          </p>
          <p class="summary__row"><i class="fas fa-user-friends"></i> {{ $reservation->number_of_guests }}名</p>
        </div>
      </div>

      <div class="section-divider"></div>

      {{-- フォーム --}}
      <form method="POST" action="{{ route('user.reviews.store', $reservation) }}" id="review-form">
        @csrf

        <!-- タイトル -->
        <div class="field">
          <label for="title" class="label">タイトル</label>
          <input id="title" name="title" type="text"
            value="{{ old('title') }}"
            maxlength="80" placeholder="例）コスパ最高、また行きたい！">
          <div class="help"><span id="title-count">{{ mb_strlen(old('title','')) }}</span>/80 文字</div>
          @error('title')<div class="help" style="color:#b91c1c">{{ $message }}</div>@enderror
        </div>

        <div class="field field--rating">
          <label class="label">評価を選択（★1〜5）</label>
          <div class="star-group" id="rating-stars">
            @for ($i = 5; $i >= 1; $i--)
            <input type="radio" name="rating" id="star-{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }}>
            <label for="star-{{ $i }}" class="star" aria-label="{{ $i }} 星">★</label>
            @endfor
          </div>
          @error('rating')<div class="help" style="color:#b91c1c">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="comment" class="label">コメント（任意）</label>
          <textarea id="comment" name="comment" maxlength="2000" placeholder="お店の感想をどうぞ">{{ old('comment') }}</textarea>
          <div class="help"><span id="char-count">{{ mb_strlen(old('comment','')) }}</span>/2000 文字</div>
          @error('comment')<div class="help" style="color:#b91c1c">{{ $message }}</div>@enderror
        </div>

        <div class="actions">
          <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
            <i class="fas fa-paper-plane"></i> 投稿する
          </button>
          <a href="{{ route('user.mypage.index') }}" class="btn">キャンセル</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
  // rating が選ばれるまで送信ボタンを無効化
  const submitBtn = document.getElementById('submit-btn');
  const ratingInputs = document.querySelectorAll('input[name="rating"]');
  const initChecked = document.querySelector('input[name="rating"]:checked');
  submitBtn.disabled = !initChecked;

  ratingInputs.forEach(r => r.addEventListener('change', () => {
    submitBtn.disabled = !document.querySelector('input[name="rating"]:checked');
  }));

  // 文字数カウント
  const ta = document.getElementById('comment');
  const counter = document.getElementById('char-count');

  function updateCount() {
    counter.textContent = (ta.value || '').length;
  }
  ta.addEventListener('input', updateCount);
  updateCount();
</script>
@endsection