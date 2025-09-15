<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'reservation_id',
        'title',
        'rating',
        'comment',
        'skipped_at',
        'display_name',
    ];

    protected $casts = [
        'skipped_at' => 'datetime',
        'rating'     => 'integer',
    ];

    // リレーション定義
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class)->withTrashed();
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
