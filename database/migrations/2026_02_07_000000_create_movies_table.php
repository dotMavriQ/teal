<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->string('director')->nullable();
            $table->string('imdb_id')->nullable()->index();
            $table->string('poster_url')->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('year')->unsigned()->nullable();
            $table->unsignedSmallInteger('runtime_minutes')->nullable();
            $table->string('genres')->nullable();
            $table->string('imdb_url')->nullable();
            $table->string('title_type')->nullable();
            $table->string('status')->default('watchlist');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->decimal('imdb_rating', 3, 1)->nullable();
            $table->unsignedInteger('num_votes')->nullable();
            $table->date('date_rated')->nullable();
            $table->date('release_date')->nullable();
            $table->date('date_watched')->nullable();
            $table->date('date_added')->nullable();
            $table->text('notes')->nullable();
            $table->text('review')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
