<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\User;
use App\Enums\WatchingStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportImdbWatchlist extends Command
{
    protected $signature = 'app:import-imdb-watchlist {user_id=1}';
    protected $description = 'Import IMDb watchlist from Downloads/imdbwatchlist.csv';

    public function handle()
    {
        $filePath = database_path('imdbwatchlist.csv');
        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $user = User::find($this->argument('user_id'));
        if (!$user) {
            $this->error("User not found.");
            return 1;
        }

        $this->info("Importing IMDb Watchlist for user: {$user->name}");

        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);
        
        $importedCount = 0;
        $skippedCount = 0;

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($headers, $row);
            
            $title = $data['Title'];
            $imdbId = $data['Const'];
            $titleType = $data['Title Type'];
            
            // Skip duplicates
            if (Movie::where('user_id', $user->id)->where('imdb_id', $imdbId)->exists()) {
                $skippedCount++;
                continue;
            }

            $mapped = [
                'user_id' => $user->id,
                'title' => $title,
                'imdb_id' => $imdbId,
                'title_type' => $titleType,
                'year' => !empty($data['Year']) ? (int)$data['Year'] : null,
                'runtime_minutes' => !empty($data['Runtime (mins)']) ? (int)$data['Runtime (mins)'] : null,
                'genres' => !empty($data['Genres']) ? $data['Genres'] : null,
                'imdb_rating' => !empty($data['IMDb Rating']) ? $data['IMDb Rating'] : null,
                'status' => WatchingStatus::Watchlist->value,
                'date_added' => now(),
            ];

            if ($titleType === 'TV Episode') {
                $info = $this->parseEpisodeTitle($title);
                $mapped['show_name'] = $info['show'];
                $mapped['season_number'] = $info['season'];
                $mapped['episode_number'] = $info['episode'];
                
                // Ensure parent show exists
                $this->ensureParentShow($user, $info['show']);
            }

            Movie::create($mapped);
            $importedCount++;
        }

        fclose($file);
        $this->info("Import complete. Imported: $importedCount, Skipped: $skippedCount");
    }

    private function ensureParentShow($user, $showName)
    {
        $exists = Movie::where('user_id', $user->id)
            ->where('title', $showName)
            ->where('title_type', 'TV Series')
            ->exists();

        if (!$exists) {
            Movie::create([
                'user_id' => $user->id,
                'title' => $showName,
                'title_type' => 'TV Series',
                'status' => WatchingStatus::Watchlist->value,
                'date_added' => now(),
            ]);
        }
    }

    private function parseEpisodeTitle($title)
    {
        // Format: "Show Name: Episode Name" or "Show Name: Episode #1.1"
        if (preg_match('/^(.+?):\s+Episode\s+#(\d+)\.(\d+)$/', $title, $m)) {
            return ['show' => $m[1], 'season' => (int)$m[2], 'episode' => (int)$m[3]];
        }
        
        if (strpos($title, ':') !== false) {
            $parts = explode(':', $title);
            return ['show' => trim($parts[0]), 'season' => null, 'episode' => null];
        }

        return ['show' => $title, 'season' => null, 'episode' => null];
    }
}
