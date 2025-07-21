@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('title', 'ログイン')

@section('content')
<div class="login-page">
  <div class="login-page__container">
    <h1 class="login-page__heading">
      Login
    </h1>

    <div class="login-page__content">
      <form action="{{route('login')}}" method="post" class="login-page__form" novalidate>
        @csrf
        <!-- ログイン失敗時のエラー -->
        <div class="login-page__form-group">
          <x-error-message field="login" />
        </div>

        <div class="login-page__form-section">
          <!-- メールアドレス -->
          <div class="login-page__form-group">
            <label class="login-page__label">
              <img class="login-page__icon" src="{{ asset('images/icons/mail.svg') }}" alt="メール">
            </label>
            <input class="login-page__input"
              type="email" name="email" id="email"
              value="{{ old('email')  }}"
              placeholder="Email">
          </div>
          <x-error-message field="email" preserve />

          <!-- パスワード -->
          <div class="login-page__form-group">
            <label for="" class="login-page__label">
              <img class="login-page__icon" src="{{ asset('images/icons/password.svg') }}" alt="パスワード">
            </label>
            <input class="login-page__input"
              type="password" name="password" id="password"
              placeholder="Password">
          </div>
          <x-error-message field="password" preserve />
        </div>

        <div class="login-page__footer">
          <!-- ログイン状態を保持 -->
          <div class="login-page__remember">
            <input id="remember" type="checkbox" name="remember" value="1" class="login-page__remember-checkbox">
            <label for="remember" class="login-page__remember-label">ログイン状態を保持する</label>
          </div>
          <!-- ログインボタン -->
          <div class="login-page__button">
            <button class="login-button" type="submit">
              ログイン
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection