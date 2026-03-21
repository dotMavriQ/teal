<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('igdb_id')->nullable()->after('rawg_id');
            $table->index('igdb_id');
        });

        // Move any IGDB IDs mistakenly stored in rawg_id to igdb_id
        DB::table('games')
            ->whereNotNull('rawg_id')
            ->update(['igdb_id' => DB::raw('rawg_id'), 'rawg_id' => null]);
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['igdb_id']);
            $table->dropColumn('igdb_id');
        });
    }
};
