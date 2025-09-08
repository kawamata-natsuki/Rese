<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'     => ['required', 'email', 'max:255', 'string'],
            'password'  => ['required', 'min:8', 'max:100', 'string'],
            'remember'  => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'メールアドレスを入力してください',
            'email.email'       => 'メールまたはパスワードが違います',
            'email.max'         => 'メールまたはパスワードが違います',
            'password.required' => 'パスワードを入力してください',
            'password.min'      => 'メールまたはパスワードが違います',
            'password.max'      => 'メールまたはパスワードが違います',
        ];
    }

    public function attributes(): array
    {
        // デフォルトのエラーメッセージ内で使われるカラム名を変換
        return [
            'email'      => 'メールアドレス',
            'password'   => 'パスワード',
        ];
    }
}
