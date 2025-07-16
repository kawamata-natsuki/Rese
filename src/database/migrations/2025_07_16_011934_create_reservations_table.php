<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('user_id');
            $table->date('reservation_date'); // 予約日
            $table->time('reservation_time'); // 予約時間
            $table->unsignedTinyInteger('number_of_guests'); //予約人数
            $table->string('reservation_status')->default('reserved');
            $table->timestamp('visited_at')->nullable(); // 来店時間
            $table->softDeletes();
            $table->timestamps();

            // 外部キー制約
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
