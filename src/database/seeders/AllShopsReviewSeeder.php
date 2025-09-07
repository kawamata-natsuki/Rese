<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Support\DisplayName;
use App\Models\User;
use App\Models\Shop;
use App\Models\Reservation;
use App\Models\Review;
use App\Enums\ReservationStatus;

class AllShopsReviewSeeder extends Seeder
{
    public function run(): void
    {
        // 対象ユーザー（必要ならダミーユーザーを作成）
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'test', 'password' => Hash::make('12345678')]
        );

        // ID順で並べて、最初の1店舗を特定しやすく
        $shops = Shop::orderBy('id')->get();

        foreach ($shops as $index => $shop) {
            // 最初の店だけ 10 件、他は 1 件
            $reviewCount = ($index === 0) ? 10 : 1;

            for ($i = 0; $i < $reviewCount; $i++) {
                DB::transaction(function () use ($user, $shop) {
                    // まず「既存の来店済み・未レビュー予約」を1件使い回し（最初の店の2件目以降は新規作成するので基本ヒットしない想定）
                    $reservation = Reservation::query()
                        ->where('user_id', $user->id)
                        ->where('shop_id', $shop->id)
                        ->where('reservation_status', ReservationStatus::VISITED)
                        ->whereDoesntHave('review')
                        ->orderByDesc('reservation_date')
                        ->first();

                    // 無ければ新しく「来店済み予約」を作成
                    if (!$reservation) {
                        // 直近60日以内で適当な日時を作成（半端な分は 00 / 30）
                        $dt = Carbon::now()
                            ->subDays(rand(1, 60))
                            ->setTime(rand(11, 21), [0, 30][rand(0, 1)]);

                        $reservation = Reservation::create([
                            'shop_id'            => $shop->id,
                            'user_id'            => $user->id,
                            'reservation_date'   => $dt->toDateString(),
                            'reservation_time'   => $dt,
                            'number_of_guests'   => rand(1, 4),
                            'reservation_status' => ReservationStatus::VISITED,
                            'visited_at'         => $dt,
                        ]);
                    }

                    // 念のため二重作成を回避
                    if ($reservation->review()->exists()) {
                        return;
                    }

                    // タイトルとコメントを少しだけバリエーション
                    $titles = ['初めて来店しました', 'コスパ最高！', '落ち着いた雰囲気', 'また行きたいお店！', '家族で満足'];
                    $title  = $titles[array_rand($titles)];

                    Review::create([
                        'reservation_id' => $reservation->id,
                        'user_id'        => $reservation->user_id,
                        'shop_id'        => $reservation->shop_id,
                        'rating'         => rand(3, 5),
                        'title'          => $title,
                        'comment'        => sprintf(
                            '%s に来店。とても良かったです！(seeded)',
                            // reservation_date が Carbon キャストならそのまま format 可能
                            $reservation->reservation_date instanceof \Carbon\Carbon
                                ? $reservation->reservation_date->format('Y/m/d')
                                : Carbon::parse($reservation->reservation_date)->format('Y/m/d')
                        ),
                        // display_name カラムを使う運用の場合。nullable なら省略してもOK
                        'display_name'   => DisplayName::mask($user->name ?? ''),
                    ]);
                });
            }
        }
    }
}
