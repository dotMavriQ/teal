<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('publisher')->nullable();
            $table->smallInteger('start_year')->nullable();
            $table->integer('issue_count')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_url', 2048)->nullable();
            $table->string('comicvine_volume_id')->nullable();
            $table->string('comicvine_url', 2048)->nullable();
            $table->text('creators')->nullable();
            $table->text('characters')->nullable();
            $table->string('status')->default('want_to_read');
            $table->tinyInteger('rating')->nullable();
            $table->date('date_started')->nullable();
            $table->date('date_finished')->nullable();
            $table->date('date_added')->nullable();
            $table->text('notes')->nullable();
            $table->text('review')->nullable();
            $table->timestamp('metadata_fetched_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'comicvine_volume_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comics');
    }
};
