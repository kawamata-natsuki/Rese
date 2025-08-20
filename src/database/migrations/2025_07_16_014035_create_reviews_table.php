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
            // 退会時済みのユーザーもレビューは残す
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // 店舗はソフトデリート想定（レビューは残す）
            $table->foreignId('shop_id')->constrained();
            // 予約必須（予約に紐付かないレビューは不可）
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('rating'); // レビュー評価(1-5)
            $table->text('comment')->nullable();
            $table->timestamp('skipped_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // ユニークキー（1予約1レビュー）
            $table->unique('reservation_id');

            // 複合インデックス
            $table->index(['shop_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
