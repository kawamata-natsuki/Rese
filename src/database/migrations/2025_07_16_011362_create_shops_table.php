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

            $table->foreignId('shop_owner_id')
                ->constrained('shop_owners')
                ->cascadeOnDelete();

            $table->string('name')->unique();
            $table->text('description');
            $table->string('image_url');
            $table->time('opening_time');
            $table->time('closing_time');

            $table->foreignId('area_id')
                ->nullable()
                ->constrained('areas')
                ->nullOnDelete();
            $table->foreignId('genre_id')
                ->nullable()
                ->constrained('genres')
                ->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
