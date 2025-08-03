<?php

namespace App\Http\Requests;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationRequest extends FormRequest
{
    protected array $numberSlots = [];
    protected array $timeSlots = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $shopId = $this->input('shop_id');
        $shop = Shop::find($shopId);

        if ($shop) {
            // 営業時間から時間スロット生成
            $opening = $shop->opening_time->copy();
            $closing = $shop->closing_time;

            $timeSlots = [];
            while ($opening < $closing) {
                $timeSlots[] = $opening->format('H:i');
                $opening->addMinutes(30);
            }

            $this->timeSlots = $timeSlots;
        }

        $this->numberSlots = range(1, 10);
    }

    public function rules(): array
    {
        return [
            'date'      => ['required', 'date',    'after_or_equal:today',],
            'time' => ['required', 'date_format:H:i', Rule::in($this->timeSlots)],
            'number' => ['required', 'integer', Rule::in($this->numberSlots),],
            'shop_id' => ['required', 'exists:shops,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required'       => '日付を選択してください',
            'date.after_or_equal' => '過去の日付は選択できません',
            'time.required'       => '時刻を選択してください',
            'time.date_format'    => '時刻の形式が正しくありません',
            'time.in'             => '有効な時刻を選択してください',
            'number.required'     => '人数を選択してください',
            'number.in'           => '有効な人数を選択してください',
        ];
    }

    protected function passedValidation()
    {
        $dateTime = Carbon::createFromFormat('Y-m-d H:i', $this->date . ' ' . $this->time);

        // 今日かつ今より前の時間なら弾く
        if ($dateTime->isToday() && $dateTime->lt(now())) {
            throw ValidationException::withMessages([
                'time' => '過去の時刻は選択できません。',
            ]);
        }

        // 完全に過去の日付も弾く
        if ($dateTime->lt(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'date' => '過去の日付は選択できません。',
            ]);
        }
    }
}
