<?php

namespace App\Enums;

enum ReservationStatus: string
{
  case RESERVED = 'reserved';
  case VISITED  = 'visited';
  case CANCELLED = 'cancelled';
  case NO_SHOW  = 'no-show';

  public function label(): string
  {
    return match ($this) {
      self::RESERVED => '予約済',
      self::VISITED  => '来店済',
      self::CANCELLED => 'キャンセル',
      self::NO_SHOW  => '無断キャンセル',
    };
  }
}
