<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Map old PlayingStatus values to new BoardGameStatus values
        DB::table('board_games')->where('status', 'backlog')->update(['status' => 'want_to_play']);
        DB::table('board_games')->where('status', 'playing')->update(['status' => 'owned']);
        DB::table('board_games')->where('status', 'completed')->update(['status' => 'owned']);
        DB::table('board_games')->where('status', 'mastered')->update(['status' => 'owned']);
        DB::table('board_games')->where('status', 'shelved')->update(['status' => 'previously_owned']);

        Schema::table('board_games', function (Blueprint $table) {
            $table->dropColumn(['date_started', 'date_finished', 'ownership']);
            $table->decimal('bgg_rating', 4, 2)->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('board_games', function (Blueprint $table) {
            $table->date('date_started')->nullable();
            $table->date('date_finished')->nullable();
            $table->string('ownership')->default('owned');
            $table->dropColumn('bgg_rating');
        });

        DB::table('board_games')->where('status', 'want_to_play')->update(['status' => 'backlog']);
        DB::table('board_games')->where('status', 'wishlist')->update(['status' => 'backlog']);
        DB::table('board_games')->where('status', 'for_trade')->update(['status' => 'shelved']);
        DB::table('board_games')->where('status', 'previously_owned')->update(['status' => 'shelved']);
    }
};
