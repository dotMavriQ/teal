<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('show_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->unsignedSmallInteger('season_number')->nullable();
            $table->unsignedSmallInteger('episode_number')->nullable();
            $table->string('imdb_id')->nullable()->index();
            $table->string('imdb_url')->nullable();
            $table->string('director')->nullable();
            $table->unsignedSmallInteger('runtime_minutes')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->decimal('imdb_rating', 3, 1)->nullable();
            $table->unsignedInteger('num_votes')->nullable();
            $table->date('date_rated')->nullable();
            $table->date('release_date')->nullable();
            $table->date('date_watched')->nullable();
            $table->string('genres')->nullable();
            $table->smallInteger('year')->unsigned()->nullable();
            $table->timestamps();

            $table->index(['show_id', 'season_number', 'episode_number']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
