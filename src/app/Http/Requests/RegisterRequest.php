<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'max:50', 'string'],
            'email'     => ['required', 'email', 'max:255', 'unique:users', 'string'],
            'password'  => ['required', 'min:8', 'max:100', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => '名前を入力してください',
            'name.max'              => '名前は50文字以内で入力してください',
            'email.required'        => 'メールアドレスを入力してください。',
            'email.email'           => 'メールアドレスの形式が正しくありません',
            'email.max'             => 'メールアドレスは255文字以内で入力してください',
            'email.unique'          => 'このメールアドレスは既に登録されています',
            'password.required'     => 'パスワードを入力してください',
            'password.min'          => 'パスワードは8文字以上で入力してください',
            'password.max'          => 'パスワードは100文字以内で入力してください',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'     => '名前',
            'email'    => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }
}
