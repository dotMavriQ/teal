<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comic_issues', function (Blueprint $table) {
            $table->index('comicvine_issue_id');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->index('show_id');
        });
    }

    public function down(): void
    {
        Schema::table('comic_issues', function (Blueprint $table) {
            $table->dropIndex(['comicvine_issue_id']);
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropIndex(['show_id']);
        });
    }
};
