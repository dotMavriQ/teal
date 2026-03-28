<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('artist');
            $table->string('tour_name')->nullable();
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->date('event_date')->nullable();
            $table->jsonb('setlist')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('status')->default('attended');
            $table->unsignedSmallInteger('rating')->nullable();
            $table->string('setlist_fm_id')->nullable();
            $table->string('artist_mbid')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('setlist_fm_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concerts');
    }
};
