<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing comma-separated genre strings to JSON arrays
        $games = DB::table('games')->whereNotNull('genre')->get(['id', 'genre']);

        foreach ($games as $game) {
            $genres = array_map('trim', explode(',', $game->genre));
            $genres = array_values(array_filter($genres, fn ($g) => $g !== ''));

            DB::table('games')
                ->where('id', $game->id)
                ->update(['genre' => json_encode($genres)]);
        }

        // Change column type to jsonb
        DB::statement('ALTER TABLE games ALTER COLUMN genre TYPE jsonb USING genre::jsonb');
    }

    public function down(): void
    {
        // Convert back to string
        $games = DB::table('games')->whereNotNull('genre')->get(['id', 'genre']);

        DB::statement('ALTER TABLE games ALTER COLUMN genre TYPE varchar(500) USING genre::varchar');

        foreach ($games as $game) {
            $genres = json_decode($game->genre, true);
            if (is_array($genres)) {
                DB::table('games')
                    ->where('id', $game->id)
                    ->update(['genre' => implode(', ', $genres)]);
            }
        }
    }
};
