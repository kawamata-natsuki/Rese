<?php

namespace App\Support;

class DisplayName
{
  /**
   * ユーザー名を「先頭＋（必要に応じて末尾）だけ残して中間を＊化」。
   */
  public static function mask(?string $name): string
  {
    $name = trim((string)$name);
    if ($name === '') return '匿名ユーザー';

    $len = mb_strlen($name);
    if ($len === 1) return $name;           // 1文字はそのまま
    if ($len === 2) return mb_substr($name, 0, 1) . '*'; // 2文字は1文字だけ残す

    $first = mb_substr($name, 0, 1);
    $last  = mb_substr($name, -1, 1);
    $midLen = $len - 2;

    return $first . str_repeat('*', $midLen) . $last;
  }
}
