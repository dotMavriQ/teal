<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\WatchingStatus;
use App\Models\Movie;
use App\Services\TmdbService;
use App\Services\TraktService;
use Illuminate\Console\Command;
use Illuminate\Support\Sleep;

class EnrichMovies extends Command
{
    protected $signature = 'app:enrich-movies {--limit=50}';

    protected $description = 'Enrich movies and TV shows with metadata from TMDB and Trakt';

    public function handle(TmdbService $tmdb, TraktService $trakt): int
    {
        $limit = (int) $this->option('limit');

        $movies = Movie::whereNotNull('imdb_id')
            ->where('status', WatchingStatus::Watchlist->value)
            ->where(function ($q): void {
                $q->whereNull('poster_url')
                    ->orWhereNull('description');
            })
            ->take($limit)
            ->get();

        if ($movies->isEmpty()) {
            $this->info('No movies need enrichment.');

            return self::SUCCESS;
        }

        $this->info("Enriching {$movies->count()} items...");

        foreach ($movies as $movie) {
            $imdbId = $movie->imdb_id;

            if (! is_string($imdbId)) {
                continue;
            }

            $this->line("Processing: {$movie->title} ({$imdbId})");

            $data = $tmdb->findByImdbId($imdbId);

            if (! $data) {
                $this->warn('  TMDB miss, trying Trakt...');
                $data = $trakt->findByImdbId($imdbId);
            }

            if ($data) {
                $updates = array_filter([
                    'poster_url' => $movie->poster_url ?: ($data['poster_url'] ?? null),
                    'description' => $movie->description ?: ($data['description'] ?? null),
                    'runtime_minutes' => $movie->runtime_minutes ?: ($data['runtime_minutes'] ?? null),
                    'genres' => $movie->genres ?: ($data['genres'] ?? null),
                    'director' => $movie->director ?: ($data['director'] ?? null),
                    'year' => $movie->year ?: ($data['year'] ?? null),
                    'metadata_fetched_at' => now(),
                ]);

                $movie->update($updates);
                $this->info('  Updated metadata.');

                // If this is a show name, propagate the poster to episodes
                if (($movie->title_type === 'TV Series' || $movie->title_type === 'TV Mini Series') && $movie->poster_url) {
                    Movie::propagateShowPoster(
                        $movie->user_id,
                        $movie->title,
                        $movie->title,
                        $movie->poster_url,
                        $movie->title
                    );
                }
            } else {
                $this->error("  No metadata found for {$imdbId}");
                $movie->update(['metadata_fetched_at' => now()]);
            }

            // Simple rate limit protection (4 requests per second)
            Sleep::usleep(250000);
        }

        $this->info('Enrichment complete.');

        return self::SUCCESS;
    }
}
