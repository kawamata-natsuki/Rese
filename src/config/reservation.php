<?php

return [
  // 予約時刻から no-show と判断するまでの猶予（分）
  'grace_minutes' => env('RESERVATION_GRACE_MINUTES', 20),
];
