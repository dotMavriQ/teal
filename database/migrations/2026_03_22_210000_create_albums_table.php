<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('artist');
            $table->jsonb('genre')->nullable();
            $table->jsonb('styles')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('format')->nullable();
            $table->string('label')->nullable();
            $table->string('country')->nullable();
            $table->string('cover_url')->nullable();
            $table->jsonb('tracklist')->nullable();
            $table->string('status')->default('listened');
            $table->string('ownership')->default('owned');
            $table->unsignedSmallInteger('rating')->nullable();
            $table->unsignedBigInteger('discogs_id')->nullable();
            $table->unsignedBigInteger('discogs_master_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('discogs_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
