<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Shop;
use App\Models\Reservation;
use App\Models\Review;
use App\Enums\ReservationStatus;

class AllShopsReviewSeeder extends Seeder
{
    public function run(): void
    {
        // 対象ユーザー（お好みで変更OK）
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'test', 'password' => Hash::make('12345678')]
        );

        $shops = Shop::all();
        foreach ($shops as $shop) {
            DB::transaction(function () use ($user, $shop) {
                // 既存の「来店済み・未レビュー」の予約があればそれを使う
                $reservation = Reservation::query()
                    ->where('user_id', $user->id)
                    ->where('shop_id', $shop->id)
                    ->where('reservation_status', ReservationStatus::VISITED)
                    ->whereDoesntHave('review')
                    ->orderByDesc('reservation_date')
                    ->first();

                // 無ければ新規に「来店済み予約」を作る
                if (!$reservation) {
                    $dt = Carbon::now()->subDays(rand(1, 30))->setTime(rand(11, 20), [0, 30][rand(0, 1)]);
                    $reservation = Reservation::create([
                        'shop_id'            => $shop->id,
                        'user_id'            => $user->id,
                        'reservation_date'   => $dt->toDateString(),
                        'reservation_time'   => $dt, // datetime キャスト想定
                        'number_of_guests'   => rand(1, 4),
                        'reservation_status' => ReservationStatus::VISITED,
                        'visited_at'         => $dt,
                    ]);
                }

                // 既にレビューが付いていたらスキップ
                if ($reservation->review()->exists()) return;

                // レビューを作成
                Review::create([
                    'reservation_id' => $reservation->id,
                    'user_id'        => $user->id,
                    'shop_id'        => $reservation->shop_id,   // ← これが必須
                    'rating'         => rand(3, 5),
                    'comment'        => sprintf(
                        '%s に来店。とても良かったです！(seeded)',
                        $reservation->reservation_date->format('Y/m/d')
                    ),
                ]);
            });
        }
    }
}
