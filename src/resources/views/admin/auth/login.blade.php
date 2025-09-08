@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('title', '管理者ログイン')

@section('content')
<div class="login-page admin-login">
  <div class="login-page__container">
    <h1 class="login-page__heading">
      管理者ログイン <span class="admin-badge">管理者専用</span>
    </h1>

    <div class="login-page__content">
      <form action="{{ route('admin.login') }}" method="post" class="login-page__form" novalidate>
        @csrf

        <div class="login-page__form-group">
          <x-error-message field="login" />
        </div>

        <div class="login-page__form-section">
          <div class="login-page__form-group">
            <label for="email" class="login-page__label">
              <i class="fas fa-envelope login-page__icon"></i>
            </label>
            <input class="login-page__input"
              type="email" name="email" id="email"
              value="{{ old('email') }}"
              autocomplete="username"
              placeholder="Email" required autofocus>
          </div>
          <x-error-message field="email" preserve />

          <div class="login-page__form-group">
            <label for="password" class="login-page__label">
              <i class="fas fa-lock login-page__icon"></i>
            </label>
            <input class="login-page__input"
              type="password" name="password" id="password"
              autocomplete="current-password"
              placeholder="Password" required>
          </div>
          <x-error-message field="password" preserve />
        </div>

        <div class="login-page__footer">
          <div class="login-page__remember">
            <input id="remember" type="checkbox" name="remember" value="1" class="login-page__remember-checkbox">
            <label for="remember" class="login-page__remember-label">ログイン状態を保持する</label>
          </div>

          <div class="login-page__button">
            <button class="login-button" type="submit">Login</button>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>
@endsection