<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->tinyInteger('rating'); // レビュー評価(1-5)
            $table->text('comment');
            $table->softDeletes();
            $table->timestamps();
            $table->timestamp('skipped_at')->nullable();

            // 複合ユニークキー
            $table->unique(['user_id', 'reservation_id']);

            // 外部キー制約
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
            $table->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
