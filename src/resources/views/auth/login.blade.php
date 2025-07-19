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
      <form action="{{route('login')}}" method="" class="login-page__form" novalidate>
        @csrf

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
            <x-error-message field="email" preserve />
          </div>

          <!-- パスワード -->
          <div class="login-page__form-group">
            <label for="" class="login-page__label">
              <img class="login-page__icon" src="{{ asset('images/icons/password.svg') }}" alt="パスワード">
            </label>
            <input class="login-page__input"
              type="password" name="password" id="password"
              placeholder="Password">
            <x-error-message field="password" preserve />
          </div>
        </div>

        <!-- ログインボタン -->
        <div class="login-page__button">
          <button class="login-button" type="submit">
            ログイン
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection