<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('games')->where('status', 'want_to_play')->update(['status' => 'backlog']);
        DB::table('games')->where('status', 'played')->update(['status' => 'completed']);
    }

    public function down(): void
    {
        DB::table('games')->where('status', 'backlog')->update(['status' => 'want_to_play']);
        DB::table('games')->where('status', 'completed')->update(['status' => 'played']);
        DB::table('games')->where('status', 'shelved')->update(['status' => 'want_to_play']);
        DB::table('games')->where('status', 'mastered')->update(['status' => 'played']);
    }
};
