@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/reservations/done.css') }}">
@endsection

@section('title', '予約完了')

@section('content')
<div class="done-page">
  <div class="done-page__card">
    <h1 class="done-page__title">ご予約が完了しました</h1>
    <p class="done-page__message">ご予約ありがとうございます。<br>予約内容はマイページから確認できます。</p>

    <div class="done-page__button-wrapper">
      <a href="{{ route('user.mypage.index') }}" class="done-page__button">マイページへ</a>
      <a href="{{ route('shop.index') }}" class="done-page__button done-page__button--secondary">トップに戻る</a>
    </div>
  </div>
</div>
@endsection