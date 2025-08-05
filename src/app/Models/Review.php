<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'shop_id',
        'reservation_id',
        'rating',
        'comment',
        'skipped_at',
    ];

    protected $casts = [
        'skipped_at' => 'datetime',
    ];

    // リレーション定義
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
