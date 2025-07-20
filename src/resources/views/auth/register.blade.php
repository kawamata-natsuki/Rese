@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('title', '会員登録')

@section('content')
<div class="register-page">
  <div class="register-page__container">
    <h1 class="register-page__heading">
      Registration
    </h1>

    <div class="register-page__content">
      <form action="{{route('register')}}" method="" class="login-page__form" novalidate>
        @csrf

        <div class="register-page__form-section">
          <!-- 名前 -->
          <div class="register-page__form-group">
            <label for="name" class="register-page__label">
              <img class="register-page__icon" src="{{ asset('images/icons/user.svg') }}" alt="名前">
            </label>
            <input class="register-page__input"
              type="text" name="name" id="name"
              value="{{ old('name')  }}"
              placeholder="Username">
            <x-error-message field="name" preserve />
          </div>

          <!-- メールアドレス -->
          <div class="register-page__form-group">
            <label for="email" class="register-page__label">
              <img class="register-page__icon" src="{{ asset('images/icons/mail.svg') }}" alt="メール">
            </label>
            <input class="register-page__input"
              type="email" name="email" id="email"
              value="{{ old('email')  }}"
              placeholder="Email">
            <x-error-message field="email" preserve />
          </div>

          <!-- パスワード -->
          <div class="register-page__form-group">
            <label for="password" class="register-page__label">
              <img class="register-page__icon" src="{{ asset('images/icons/password.svg') }}" alt="パスワード">
            </label>
            <input class="register-page__input"
              type="password" name="password" id="password"
              placeholder="Password">
            <x-error-message field="password" preserve />
          </div>
        </div>

        <!-- 登録ボタン -->
        <div class="register-page__button">
          <button class="register-button" type="submit">
            登録
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection