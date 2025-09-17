<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairReservationConsistency extends Command
{
  protected $signature = 'reservations:repair-consistency {--dry-run : 実行せず件数のみ表示}';
  protected $description = 'レビューがあるのに VISITED でない予約を VISITED に修復する';

  public function handle(): int
  {
    $dry = (bool) $this->option('dry-run');

    $q = Reservation::query()
      ->whereIn('reservation_status', [ReservationStatus::RESERVED, ReservationStatus::CANCELLED, ReservationStatus::NO_SHOW])
      ->whereHas('review')
      ->whereNull('visited_at');

    $count = (clone $q)->count();
    if ($dry) {
      $this->info("Will fix {$count} reservations (dry-run)");
      return self::SUCCESS;
    }

    $fixed = 0;
    DB::transaction(function () use ($q, &$fixed) {
      $q->chunkById(500, function ($rows) use (&$fixed) {
        foreach ($rows as $r) {
          $r->reservation_status = ReservationStatus::VISITED;
          $r->visited_at = $r->startsAt();
          $r->save();
          $fixed++;
        }
      });
    });

    $this->info("Fixed {$fixed} reservations");
    return self::SUCCESS;
  }
}
