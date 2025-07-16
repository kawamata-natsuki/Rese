<?php

namespace App\Enums;

enum Role: string
{
  case USER   = 'user';
  case ADMIN  = 'admin';
  case OWNER  = 'owner';

  public function label(): string
  {
    return match ($this) {
      self::USER    => '一般ユーザー',
      self::ADMIN   => '管理者',
      self::OWNER   => '店舗代表者',
    };
  }
}
