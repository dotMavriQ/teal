<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('show_name')->nullable()->after('title');
            $table->unsignedSmallInteger('season_number')->nullable()->after('show_name');
            $table->unsignedSmallInteger('episode_number')->nullable()->after('season_number');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['show_name', 'season_number', 'episode_number']);
        });
    }
};
