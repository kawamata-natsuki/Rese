<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Reservation;
use App\Models\Review;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // きれいにしたい場合はコメントアウト解除
        // Review::truncate();
        // Reservation::truncate();
        // User::where('email', '!=', 'admin@example.com')->delete();

        // --- Helper: ポアソン乱数 ---
        $poisson = function (float $lambda): int {
            if ($lambda <= 0) return 0;
            $L = exp(-$lambda);
            $k = 0;
            $p = 1.0;
            do {
                $k++;
                $p *= mt_rand() / mt_getrandmax();
            } while ($p > $L);
            return $k - 1;
        };

        // ダミーの一般ユーザーを少し用意（作成日はバラす）
        User::factory()->count(60)->state(function () {
            $d = now()->copy()->subDays(rand(0, 29))
                ->setTime(rand(8, 22), rand(0, 59), 0);
            return ['created_at' => $d, 'updated_at' => $d];
        })->create();

        $shops = Shop::query()->pluck('id');             // 既存のショップを利用
        $users = User::query()->pluck('id');             // 一般ユーザーID一覧
        if ($shops->isEmpty()) {
            // 念のため: ショップが無ければ1件作る
            $shops = collect([Shop::factory()->create()->id]);
        }

        // 時間帯の候補（レストラン想定）
        $timeSlots = ['11:30:00', '12:00:00', '12:30:00', '18:00:00', '18:30:00', '19:00:00', '19:30:00', '20:00:00'];

        // 直近30日
        $period = CarbonPeriod::create(now()->subDays(29)->startOfDay(), '1 day', now()->startOfDay());

        foreach ($period as $day) {
            $isWeekend  = in_array($day->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY], true);

            $spikeDay   = mt_rand(1, 100) <= 10;                   // 10%でスパイク

            // 予約件数: 平日 ~1.5、週末は +2 のブースト、さらに小さな揺らぎ
            $lambdaRes  = 2.0 + ($isWeekend ? 1.5 : 0) + ($spikeDay ? 4.0 : 0) + mt_rand(-6, 6) / 10;
            $reservationsCount = max(0, $poisson($lambdaRes));   // ゼロ日を避けたければ max(1, ...)


            // 新規ユーザー: たまにスパイク
            $spike = (mt_rand(1, 100) <= 12); // 12%でスパイク
            $lambdaUsers = ($spike ? 6.0 : 1.2) + ($isWeekend ? 0.6 : 0);
            $newUsersCount = $poisson($isWeekend ? 2.0 : 1.0);

            // レビュー件数: その日の予約の一部がレビューを書くイメージ
            // → 予約生成後に決める

            // --- 新規ユーザー作成 ---
            if ($newUsersCount > 0) {
                User::factory()->count($newUsersCount)->state(function () use ($day) {
                    $created = $day->copy()->setTime(rand(9, 22), rand(0, 59), 0);
                    return ['created_at' => $created, 'updated_at' => $created];
                })->create();

                // ユーザープール更新
                $users = User::query()->pluck('id');
            }

            // --- 予約作成 ---
            $reservationsToday = collect();
            for ($i = 0; $i < $reservationsCount; $i++) {
                $shopId = $shops->random();
                $userId = $users->random();

                // 人気時間帯ほど出やすいようにバイアス
                $slot = $this->weightedPick($timeSlots, [2, 3, 2, 4, 5, 4, 3, 2]); // 夕食帯を厚めに

                $reservation = Reservation::factory()->create([
                    'shop_id'           => $shopId,
                    'user_id'           => $userId,
                    'reservation_date'  => $day->toDateString(),
                    'reservation_time'  => $slot,
                    'number_of_guests'  => $this->weightedPick([1, 2, 3, 4, 5], [2, 5, 3, 2, 1]),
                    'created_at'        => $day->copy()->setTime(rand(9, 22), rand(0, 59), 0),
                    'updated_at'        => now(),
                ]);

                $reservationsToday->push($reservation);
            }

            // --- ステータス割り当て（現実的な比率） ---
            // 過去日: visited ~78%, cancelled ~12%, no-show ~10%
            if ($reservationsToday->isNotEmpty()) {
                $isPastDay = $day->lt(now()->startOfDay());
                if ($isPastDay) {
                    foreach ($reservationsToday as $r) {
                        $rand = mt_rand(1, 100);
                        if ($rand <= 78) {
                            $starts = $r->startsAt();
                            $r->update([
                                'reservation_status' => \App\Enums\ReservationStatus::VISITED,
                                'visited_at'         => $starts->copy()->addMinutes(rand(0, 90)),
                            ]);
                        } elseif ($rand <= 90) {
                            $r->update(['reservation_status' => \App\Enums\ReservationStatus::CANCELLED]);
                        } else {
                            $r->update(['reservation_status' => \App\Enums\ReservationStatus::NO_SHOW]);
                        }
                    }
                }

                // --- レビュー作成（来店済みの一部に絞る） ---
                $visited = $reservationsToday->filter(fn($r) => $r->reservation_status === \App\Enums\ReservationStatus::VISITED);
                if ($visited->isNotEmpty()) {
                    $ratio = (mt_rand(30, 60) + ($spikeDay ? 10 : 0)) / 100; // 30-60%
                    $pick  = max(0, (int) round($visited->count() * $ratio));
                    foreach ($visited->random($pick) as $r) {
                        if ($r->review()->exists()) continue;
                        Review::factory()->create([
                            'reservation_id' => $r->id,
                            'shop_id'        => $r->shop_id,
                            'user_id'        => $r->user_id,
                            'display_name'   => $r->user->name ?? 'ゲスト',
                            // 予約日の1〜5日後くらいにレビュー投稿
                            'created_at'     => $r->reservation_date
                                ? Carbon::parse($r->reservation_date)->addDays(rand(0, 5))->setTime(rand(10, 22), rand(0, 59), 0)
                                : $day->copy()->setTime(rand(12, 23), rand(0, 59), 0),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * 簡易重み付き抽選
     * @param  array $values
     * @param  array<int> $weights
     */
    private function weightedPick(array $values, array $weights)
    {
        $sum = array_sum($weights);
        $r = mt_rand(1, max(1, $sum));
        $acc = 0;
        foreach ($values as $i => $v) {
            $acc += ($weights[$i] ?? 1);
            if ($r <= $acc) return $v;
        }
        return $values[array_key_first($values)];
    }
}
