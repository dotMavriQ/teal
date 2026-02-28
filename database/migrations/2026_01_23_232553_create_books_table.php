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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn', 13)->nullable()->index();
            $table->string('isbn13', 17)->nullable()->index();
            $table->string('cover_url')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->date('published_date')->nullable();
            $table->string('publisher')->nullable();
            $table->string('goodreads_id')->nullable()->index();
            $table->string('status')->default('want_to_read');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->date('date_started')->nullable();
            $table->date('date_finished')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
