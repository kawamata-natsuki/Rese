<?php

// database/factories/ReviewFactory.php
namespace Database\Factories;

use App\Models\Review;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $reservation = Reservation::inRandomOrder()->first() ?? Reservation::factory()->create();

        // 日付と時刻を安全に合体
        $date = Carbon::parse($reservation->reservation_date)->format('Y-m-d');
        $time = Carbon::parse($reservation->reservation_time)->format('H:i:s');
        $reservationAt = Carbon::createFromFormat('Y-m-d H:i:s', "$date $time");

        $reviewAt = $reservationAt->copy()->addHours(rand(1, 72));
        if ($reviewAt->gt(now())) {
            $reviewAt = now()->subHours(rand(1, 24));
        }

        // ★ 長さ制御（必要なら display_name も）
        $displayName = $reservation->user->name ?? $this->faker->name();

        return [
            'reservation_id' => $reservation->id,
            'shop_id'        => $reservation->shop_id,
            'user_id'        => $reservation->user_id,
            'display_name'   => Str::limit($displayName, 50),                // 例: 50文字まで
            'rating'         => $this->faker->numberBetween(3, 5),
            'title'          => Str::limit($this->faker->sentence(6), 60),   // 例: 60文字まで
            'comment'        => Str::limit($this->faker->paragraph(3), 300), // 例: 300文字まで
            'created_at'     => $reviewAt,
            'updated_at'     => $reviewAt,
        ];
    }
}
