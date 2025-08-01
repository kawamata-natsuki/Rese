@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/shop/show.css') }}">
@endsection

@section('title', '飲食店詳細')

@section('content')
<div class="shop-show-page">
  <div class="shop-show-page__container">

    <!-- 左側：店舗情報 -->
    <div class="shop-show-page__info-wrapper">

      <!-- 店舗名 -->
      <p class="shop-show-page__name">{{ $shop->name }}</p>

      <!-- 店舗画像 -->
      <div class="shop-show-page__image-wrapper">
        <img src="{{ $shop->image_url }}" alt="{{ $shop->name }}の画像" class="shop-show-page__image">
      </div>

      <!-- エリア＋ジャンル -->
      <div class="shop-show-page__tags">
        <span class="shop-show-page__tag is-area">
          # {{ $shop->area->name }}
        </span>
        <span class="shop-show-page__tag is-genre">
          # {{ $shop->genre->name }}
        </span>
      </div>

      <!-- 店舗紹介 -->
      <p class="shop-show-page__description">
        {{ $shop->description }}
      </p>
    </div>

    <!-- 右側：予約フォーマット -->
    <div class="reservation-section">
      <form action="{{ route('user.reservations.store') }}" method="post" class="reservation-form">
        <h1 class="reservation-form__heading">予約</h1>

        <!-- 日にち選択 -->
        <!-- 時間選択 -->
        <!-- 人数選択 -->

        <!-- フォーム入力結果表示 -->

        <!-- 予約するボタン -->
      </form>
    </div>

  </div>

</div>



@endsection