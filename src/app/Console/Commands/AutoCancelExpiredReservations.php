<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;

class AutoCancelExpiredReservations extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'reservations:auto-noshow';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'グレース経過後、RESERVED を NO_SHOW（無断キャンセル）に自動更新する';

  /**
   * Execute the console command.
   */
  public function handle(): int
  {
    $grace = (int) config('reservation.grace_minutes', 20);
    $threshold = now()->subMinutes($grace);

    // まだチェックインしておらず（visited_at null）かつレビュー未投稿、開始日時 + 猶予 < 現在 の予約を no-show に更新
    $affected = Reservation::query()
      ->where('reservation_status', ReservationStatus::RESERVED)
      ->whereNull('visited_at')
      ->whereDoesntHave('review')
      ->whereRaw(
        "STR_TO_DATE(CONCAT(reservation_date, ' ', reservation_time), '%Y-%m-%d %H:%i:%s') < ?",
        [$threshold]
      )
      ->update([
        'reservation_status' => ReservationStatus::NO_SHOW->value,
      ]);

    $this->info("Auto marked no-show reservations: {$affected}");

    return self::SUCCESS;
  }
}
