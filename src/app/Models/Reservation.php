<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id',
        'user_id',
        'reservation_date',
        'reservation_time',
        'number_of_guests',
        'reservation_status',
        'visited_at',
    ];

    protected $casts = [
        'reservation_date'      => 'date',
        'reservation_time'      => 'datetime',
        'visited_at'            => 'datetime',
        'reservation_status'    => ReservationStatus::class,
    ];

    public function startsAt(): \Carbon\Carbon
    {
        $date = $this->reservation_date->format('Y-m-d');
        $time = $this->reservation_time->format('H:i:s');
        return Carbon::parse("$date $time");
    }

    // リレーション定義
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
