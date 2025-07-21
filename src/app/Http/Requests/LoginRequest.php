<?php

namespace App\Http\Requests;

use GuzzleHttp\Psr7\Message;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'string'],
            'password' => ['required', 'min:8', 'max:100', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email'    => 'メールアドレスは「ユーザー名@ドメイン」形式で入力してください',
            'password.required' => 'パスワードを入力してください', 
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.max' => 'パスワードは100文字以内で入力してください',
        ];
    }

    public function attributes(): array
    {
        return[
            'email'      => 'メールアドレス',
            'password'   => 'パスワード',
        ];
    }
}
