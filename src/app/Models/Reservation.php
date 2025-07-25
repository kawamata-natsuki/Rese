<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

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
        'reservation_time'      => 'time',
        'visited_at'            => 'datetime',
        'reservation_status'    => ReservationStatus::class,
    ];

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
