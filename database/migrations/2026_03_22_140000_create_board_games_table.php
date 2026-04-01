<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->jsonb('genre')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_url')->nullable();
            $table->unsignedSmallInteger('year_published')->nullable();
            $table->string('designer')->nullable();
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('min_players')->nullable();
            $table->unsignedSmallInteger('max_players')->nullable();
            $table->unsignedSmallInteger('playing_time')->nullable();
            $table->string('status')->default('backlog');
            $table->string('ownership')->default('owned');
            $table->unsignedSmallInteger('rating')->nullable();
            $table->unsignedInteger('plays')->nullable();
            $table->unsignedBigInteger('bgg_id')->nullable();
            $table->date('date_started')->nullable();
            $table->date('date_finished')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('bgg_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_games');
    }
};
