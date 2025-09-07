@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="{{ asset('css/user/reviews/create.css') }}">
@endsection

@section('title', 'レビュー投稿')

@section('content')
<div class="reviews-create-page" id="review-root" data-reservation-id="{{ $reservation->id }}">
  <div class="reviews-create-page__container">
    <h1 class="reviews-create-page__heading">REVIEW</h1>

    <div class="reviews-create-page__section">
      {{-- 予約サマリ（画像16:9＋情報） --}}
      <div class="reviews-create-page__summary">
        <div class="reviews-create-page__summary-body">
          <div class="reviews-create-page__summary-media">
            <img
              src="{{ $reservation->shop->image_url }}"
              alt="{{ $reservation->shop->name }}"
              class="reviews-create-page__summary-img"
              loading="lazy" decoding="async">
          </div>

          <div class="reviews-create-page__summary-info">
            <p class="reviews-create-page__summary-shop">
              <i class="fas fa-store review-page__icon"></i>
              <span>{{ $reservation->shop->name }}</span>
            </p>
            <p class="reviews-create-page__summary-date">
              <i class="far fa-calendar review-page__icon"></i>
              <span>{{ $reservation->reservation_date->format('Y年n月j日（'.['日','月','火','水','木','金','土'][$reservation->reservation_date->dayOfWeek].'）') }}</span>
            </p>
            <p class="reviews-create-page__summary-time">
              <i class="far fa-clock review-page__icon"></i>
              <span>{{ $reservation->reservation_time->format('H:i') }}</span>
            </p>
            <p class="reviews-create-page__summary-guest">
              <i class="fas fa-user-friends review-page__icon"></i>
              <span>{{ $reservation->number_of_guests }}名</span>
            </p>
          </div>
        </div>
      </div>


    </div>

    <div class="reviews-create-page__section">
      <!-- 公開ポリシーの告知（匿名化） -->
      <p class="reviews-create-page__notice" style="margin-top:8px;color:#6b7280;font-size:.9rem;">
        ※ 公開時は <strong>マスク済みのユーザー名</strong> で表示されます（例：な**き / N****i）。<br>
        個人が特定される情報は表示されません。
      </p>

      <div class="reviews-create-page__form-card">
        <!-- 必須説明 -->
        <p class="form-note">※ <span class="required-mark">*</span> が付いた項目は入力必須です。</p>
        <form method="POST" action="{{ route('user.reviews.store', $reservation) }}" id="review-form">
          @csrf

          <!-- タイトル（必須） -->
          <div class="field">
            <label for="title" class="label">タイトル <span class="required-mark">*</span></label>
            <div class="field-control">
              <input id="title" name="title" type="text"
                value="{{ old('title') }}"
                maxlength="80"
                placeholder="例）コスパ最高、また行きたい！"
                autocomplete="off" required>
              <div class="help"><span id="title-count">{{ mb_strlen(old('title','')) }}</span>/80 文字</div>
            </div>
          </div>

          {{-- 評価（必須） --}}
          <div class="field field--rating">
            <label class="label">お店の評価 <span class="required-mark">*</span></label>
            <div class="field-control"> {{-- ★ これを追加（タイトル/コメントと同じ器） --}}
              <div class="star-group" id="rating-stars" role="radiogroup" aria-label="星の評価">
                @for ($i = 5; $i >= 1; $i--)
                <input type="radio" name="rating" id="star-{{ $i }}" value="{{ $i }}"
                  {{ old('rating') == $i ? 'checked' : '' }} required>
                <label for="star-{{ $i }}" class="star" aria-label="{{ $i }} 星">★</label>
                @endfor
              </div>
            </div>
          </div>

          <!-- コメント（任意） -->
          <div class="field">
            <label for="comment" class="label">コメント</label>
            <div class="field-control">
              <textarea id="comment" name="comment" maxlength="2000" placeholder="お店の感想をどうぞ">{{ old('comment') }}</textarea>
              <div class="help"><span id="char-count">{{ mb_strlen(old('comment','')) }}</span>/2000 文字</div>
            </div>
          </div>

          <div class="actions">
            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
              <i class="fas fa-paper-plane"></i>
              <span class="btn-text">投稿する</span>
              <span class="btn-spinner" aria-hidden="true" style="display:none;margin-left:.5rem;">
                <i class="fas fa-circle-notch fa-spin"></i>
              </span>
            </button>
            <a href="{{ route('user.mypage.index') }}" class="btn">キャンセル</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
  (function() {
    function ready(fn) {
      document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function() {
      const root = document.getElementById('review-root'); // data-reservation-id を持つルート
      const form = document.getElementById('review-form');
      const submitBtn = document.getElementById('submit-btn');
      const titleEl = document.getElementById('title');
      const commentEl = document.getElementById('comment');
      const titleCount = document.getElementById('title-count');
      const commentCount = document.getElementById('char-count');
      const ratingSelector = 'input[name="rating"]';

      // 文字数カウント
      function updTitle() {
        if (titleCount && titleEl) titleCount.textContent = String((titleEl.value || '').length);
      }

      function updComment() {
        if (commentCount && commentEl) commentCount.textContent = String((commentEl.value || '').length);
      }
      updTitle();
      updComment();
      if (titleEl) titleEl.addEventListener('input', updTitle);
      if (commentEl) commentEl.addEventListener('input', updComment);

      // ボタン活性条件：タイトル && ★
      function hasTitle() {
        return !!(titleEl && titleEl.value.trim().length > 0);
      }

      function hasRating() {
        return !!document.querySelector(ratingSelector + ':checked');
      }

      function updateSubmitEnabled() {
        if (submitBtn) submitBtn.disabled = !(hasTitle() && hasRating());
      }
      updateSubmitEnabled();
      if (titleEl) titleEl.addEventListener('input', updateSubmitEnabled);
      document.querySelectorAll(ratingSelector).forEach(el => el.addEventListener('change', updateSubmitEnabled));

      // 二重送信防止（ブラウザの必須チェック通過時のみ）
      if (form && submitBtn) {
        form.addEventListener('submit', function() {
          if (form.checkValidity()) {
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            const t = submitBtn.querySelector('.btn-text');
            const s = submitBtn.querySelector('.btn-spinner');
            if (t) t.textContent = '送信中...';
            if (s) s.style.display = 'inline-block';
          }
        });
      }

      // 通知：このページ滞在中だけ current_rid をセット（BladeをJSに埋めない）
      const rid = root?.dataset?.reservationId;
      window.NOTIFICATION_CURRENT_RID = rid ? parseInt(rid, 10) : null;
      window.addEventListener('unload', () => {
        try {
          delete window.NOTIFICATION_CURRENT_RID;
        } catch (_) {}
      });
    });
  })();
</script>
@endsection