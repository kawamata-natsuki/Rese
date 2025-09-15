<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::inRandomOrder()->first()?->id ?? Shop::factory(),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'reservation_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'reservation_time' => $this->faker->time('H:i:s'),
            'number_of_guests' => $this->faker->numberBetween(1, 5),
        ];
    }
}
