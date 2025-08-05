@extends('layouts.app')

@section('css')
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

        @foreach($reservations as $reservation)
        <div class="mypage__reservations-item">
          <x-reservation-card :reservation="$reservation" />
        </div>
        @endforeach
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

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // 開閉用（ヘッダークリック）
    document.querySelectorAll('.reservation-card__header.js-toggle').forEach(toggle => {
      toggle.addEventListener('click', function(e) {
        if (e.target.closest('.reservation-card__close-button')) return;

        const targetId = this.dataset.target;
        const content = document.getElementById(targetId);
        if (content) {
          content.style.display = content.style.display === 'none' ? 'block' : 'none';
        } else {
          console.warn('対象が見つかりません:', targetId);
        }
      });
    });

    // 閉じるボタン（×）
    document.querySelectorAll('.reservation-card__close-button').forEach(button => {
      button.addEventListener('click', function() {
        const parent = this.closest('.reservation-card');
        if (parent) parent.style.display = 'none';
      });
    });
  });
</script>
@endsection