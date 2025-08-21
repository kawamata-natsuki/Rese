<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価（★）を選択してください。',
            'rating.between'  => '評価は1〜5の範囲で指定してください。',
            'comment.max'     => 'コメントは :max 文字以内で入力してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'rating'  => '評価',
            'comment' => 'コメント',
        ];
    }
}
