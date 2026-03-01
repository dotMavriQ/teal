<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Services\TmdbService;
use App\Services\TraktService;
use Illuminate\Console\Command;

class EnrichMovies extends Command
{
    protected $signature = 'app:enrich-movies {--limit=50}';
    protected $description = 'Enrich movies and TV shows with metadata from TMDB and Trakt';

    public function handle(TmdbService $tmdb, TraktService $trakt)
    {
        $limit = (int) $this->option('limit');
        
        $movies = Movie::whereNotNull('imdb_id')
            ->where('status', \App\Enums\WatchingStatus::Watchlist->value)
            ->where(function($q) {
                $q->whereNull('poster_url')
                  ->orWhereNull('description');
            })
            ->take($limit)
            ->get();

        if ($movies->isEmpty()) {
            $this->info("No movies need enrichment.");
            return;
        }

        $this->info("Enriching {$movies->count()} items...");

        foreach ($movies as $movie) {
            $this->line("Processing: {$movie->title} ({$movie->imdb_id})");
            
            $data = $tmdb->findByImdbId($movie->imdb_id);
            
            if (!$data) {
                $this->warn("  TMDB miss, trying Trakt...");
                $data = $trakt->findByImdbId($movie->imdb_id);
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

                if (!empty($updates)) {
                    $movie->update($updates);
                    $this->info("  Updated metadata.");
                    
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
                }
            } else {
                $this->error("  No metadata found for {$movie->imdb_id}");
            }

            // Simple rate limit protection (4 requests per second)
            usleep(250000); 
        }

        $this->info("Enrichment complete.");
    }
}
