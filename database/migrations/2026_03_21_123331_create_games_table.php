<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->jsonb('platform')->nullable();
            $table->string('genre')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_url')->nullable();
            $table->date('release_date')->nullable();
            $table->string('developer')->nullable();
            $table->string('publisher')->nullable();
            $table->string('status')->default('want_to_play');
            $table->string('ownership')->default('not_owned');
            $table->unsignedSmallInteger('rating')->nullable();
            $table->decimal('hours_played', 8, 1)->nullable();
            $table->unsignedSmallInteger('completion_percentage')->nullable();
            $table->unsignedBigInteger('rawg_id')->nullable();
            $table->unsignedBigInteger('mobygames_id')->nullable();
            $table->date('date_started')->nullable();
            $table->date('date_finished')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('rawg_id');
            $table->index('mobygames_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
