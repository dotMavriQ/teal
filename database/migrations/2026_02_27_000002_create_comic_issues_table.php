<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comic_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('issue_number')->nullable();
            $table->date('cover_date')->nullable();
            $table->string('cover_url', 2048)->nullable();
            $table->text('description')->nullable();
            $table->string('comicvine_issue_id')->nullable();
            $table->string('comicvine_url', 2048)->nullable();
            $table->string('status')->default('want_to_read');
            $table->tinyInteger('rating')->nullable();
            $table->date('date_read')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['comic_id', 'issue_number']);
            $table->index('user_id');
            $table->index(['comic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comic_issues');
    }
};
