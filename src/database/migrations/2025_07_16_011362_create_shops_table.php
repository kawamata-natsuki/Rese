<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('genre_id')->nullable();
            $table->text('description');
            $table->string('image_url');
            $table->time('opening_time');
            $table->time('closing_time');
            $table->softDeletes();
            $table->timestamps();

            // 外部キー制約
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
