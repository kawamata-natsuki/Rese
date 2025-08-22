@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('title', '会員登録')

@section('content')
<div class="register-page">
  <div class="register-page__container">
    <h1 class="register-page__heading">Sign Up</h1>

    <div class="register-page__content">
      <form action="{{ route('register') }}" method="post" class="register-page__form" novalidate>
        @csrf

        <div class="register-page__form-section">
          {{-- 名前 --}}
          <div class="register-page__form-group">
            <label for="name" class="register-page__label">
              <i class="fas fa-user register-page__icon"></i>
            </label>
            <input class="register-page__input" type="text" id="name" name="name"
              value="{{ old('name') }}" placeholder="Username">
          </div>
          <x-error-message field="name" preserve />

          {{-- メール --}}
          <div class="register-page__form-group">
            <label for="email" class="register-page__label">
              <i class="fas fa-envelope register-page__icon"></i>
            </label>
            <input class="register-page__input" type="email" id="email" name="email"
              value="{{ old('email') }}" placeholder="Email">
          </div>
          <x-error-message field="email" preserve />

          {{-- パスワード --}}
          <div class="register-page__form-group">
            <label for="password" class="register-page__label">
              <i class="fas fa-lock register-page__icon"></i>
            </label>
            <input class="register-page__input" type="password" id="password" name="password"
              placeholder="Password">
          </div>
          <x-error-message field="password" preserve />
        </div>

        <div class="register-page__button">
          <button class="register-button" type="submit">Sign Up</button>
        </div>

        <div class="register-page__link">
          <a href="{{ route('login.view') }}">
            ログインはこちら
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection