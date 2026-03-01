<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Movie;
use App\Services\TmdbService;
use App\Services\TraktService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchMovieMetadata implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 1800; // 30 minutes max

    private const CACHE_KEY_PREFIX = 'movie_metadata_fetch_';

    public function __construct(
        public int $userId,
        public array $movieIds,
        public array $sourcePriority = ['current', 'tmdb']
    ) {}

    public function handle(): void
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $this->userId;

        Cache::put($cacheKey, [
            'status' => 'running',
            'progress' => 0,
            'total' => count($this->movieIds),
            'fetched' => 0,
            'applied' => 0,
            'started_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(2));

        $tmdb = app(TmdbService::class);
        $trakt = app(TraktService::class);
        $currentFirst = $this->sourcePriority[0] === 'current';

        // Build ordered list of services based on source priority
        $sourceMap = ['trakt' => $trakt, 'tmdb' => $tmdb];
        $orderedSources = [];
        foreach ($this->sourcePriority as $source) {
            if (isset($sourceMap[$source])) {
                $orderedSources[$source] = $sourceMap[$source];
            }
        }

        $fetched = 0;
        $applied = 0;

        foreach ($this->movieIds as $index => $movieId) {
            try {
                $movie = Movie::where('user_id', $this->userId)->find($movieId);

                if (! $movie) {
                    continue;
                }

                // Detect episodes by title_type or existing episode fields
                $isEpisode = $movie->isEpisode() || $movie->title_type === 'TV Episode';

                if ($isEpisode) {
                    // Handle episodes: fetch show poster + populate episode fields from TMDB
                    $updateData = [];
                    $episodeDetails = null;

                    // Primary: use IMDb ID to get full episode details (show_name, season, episode, poster)
                    if (! empty($movie->imdb_id)) {
                        $episodeDetails = $tmdb->findEpisodeDetailsByImdbId($movie->imdb_id);
                    }

                    if ($episodeDetails) {
                        $fetched++;

                        // Populate episode fields if missing
                        if (empty($movie->show_name) && ! empty($episodeDetails['show_name'])) {
                            $updateData['show_name'] = $episodeDetails['show_name'];
                        }
                        if ($movie->season_number === null && $episodeDetails['season_number'] !== null) {
                            $updateData['season_number'] = $episodeDetails['season_number'];
                        }
                        if ($movie->episode_number === null && $episodeDetails['episode_number'] !== null) {
                            $updateData['episode_number'] = $episodeDetails['episode_number'];
                        }

                        // Update poster
                        $posterUrl = $episodeDetails['poster_url'] ?? null;
                        if ($posterUrl && (empty($movie->poster_url) || ! $currentFirst)) {
                            $updateData['poster_url'] = $posterUrl;
                        }
                    } else {
                        // Fallback: search TV shows by show_name or title prefix
                        $showName = $movie->show_name;
                        if (empty($showName) && str_contains($movie->title, ':')) {
                            $showName = trim(explode(':', $movie->title, 2)[0]);
                        }

                        if (! empty($showName)) {
                            $posterUrl = $tmdb->searchTVShowPosterByTitle($showName);
                            if ($posterUrl && (empty($movie->poster_url) || ! $currentFirst)) {
                                $updateData['poster_url'] = $posterUrl;
                                $fetched++;
                            }
                        }
                    }

                    $updateData['metadata_fetched_at'] = now();
                    $movie->update($updateData);

                    if (count($updateData) > 1) { // more than just metadata_fetched_at
                        $applied++;
                    }

                    // Propagate show poster + show_name to siblings missing them
                    $posterToPropagate = $updateData['poster_url'] ?? $movie->poster_url;
                    $showNameToPropagate = $updateData['show_name'] ?? $movie->show_name;
                    if ($posterToPropagate || $showNameToPropagate) {
                        $titlePrefix = str_contains($movie->title, ':')
                            ? trim(explode(':', $movie->title, 2)[0])
                            : null;
                        Movie::propagateShowPoster(
                            $this->userId,
                            $showNameToPropagate,
                            $titlePrefix,
                            $posterToPropagate,
                            $showNameToPropagate,
                        );
                    }
                } else {
                    // Fetch from sources in priority order, merging results
                    $metadata = null;

                    foreach ($orderedSources as $sourceName => $sourceService) {
                        $result = null;

                        if (! empty($movie->imdb_id)) {
                            $result = $sourceService->findByImdbId($movie->imdb_id);
                        }

                        // Only fall back to title search if no IMDb ID exists
                        if (! $result && empty($movie->imdb_id) && ! empty($movie->title)) {
                            $year = $sourceName === 'tmdb' ? $movie->year : null;
                            $result = $sourceService->searchByTitle($movie->title, $year);
                        }

                        if (! $result) {
                            continue;
                        }

                        if ($metadata === null) {
                            $metadata = $result;
                        } else {
                            // Fill empty fields from this source
                            foreach ($result as $key => $value) {
                                if (! empty($value) && empty($metadata[$key])) {
                                    $metadata[$key] = $value;
                                }
                            }
                        }
                    }

                    // Mark as attempted regardless of result
                    $movie->update(['metadata_fetched_at' => now()]);

                    if ($metadata) {
                        $fetched++;

                        $updateData = [];
                        $fields = ['description', 'poster_url', 'runtime_minutes', 'release_date', 'genres', 'director'];

                        foreach ($fields as $field) {
                            $hasCurrentValue = ! empty($movie->$field);
                            $hasNewValue = ! empty($metadata[$field]);

                            if (! $hasNewValue) {
                                continue;
                            }

                            if (! $hasCurrentValue) {
                                $updateData[$field] = $metadata[$field];
                            } elseif (! $currentFirst) {
                                $updateData[$field] = $metadata[$field];
                            }
                        }

                        if (! empty($updateData)) {
                            $movie->update($updateData);
                            $applied++;
                        }

                        // If this is a TV show, propagate poster to its episodes
                        $showPoster = $updateData['poster_url'] ?? $movie->poster_url;
                        if ($showPoster && in_array($movie->title_type, ['TV Series', 'TV Mini Series'])) {
                            $titlePrefix = str_contains($movie->title, ':')
                                ? trim(explode(':', $movie->title, 2)[0])
                                : $movie->title;
                            Movie::propagateShowPoster(
                                $this->userId,
                                $movie->title, // show_name to match on
                                $titlePrefix,
                                $showPoster,
                                $movie->title, // propagate show_name value
                            );
                        }
                    }
                }

                Cache::put($cacheKey, [
                    'status' => 'running',
                    'progress' => $index + 1,
                    'total' => count($this->movieIds),
                    'fetched' => $fetched,
                    'applied' => $applied,
                    'started_at' => Cache::get($cacheKey)['started_at'] ?? now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ], now()->addHours(2));

                // 300ms delay to respect TMDB rate limits
                usleep(300000);

            } catch (\Exception $e) {
                Log::warning("FetchMovieMetadata: Error fetching movie {$movieId}: " . $e->getMessage());
            }
        }

        Cache::put($cacheKey, [
            'status' => 'completed',
            'progress' => count($this->movieIds),
            'total' => count($this->movieIds),
            'fetched' => $fetched,
            'applied' => $applied,
            'started_at' => Cache::get($cacheKey)['started_at'] ?? now()->toIso8601String(),
            'completed_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(2));

        Log::info("FetchMovieMetadata: Completed for user {$this->userId}. Fetched: {$fetched}, Applied: {$applied}");
    }

    public static function getStatus(int $userId): ?array
    {
        return Cache::get(self::CACHE_KEY_PREFIX . $userId);
    }

    public static function clearStatus(int $userId): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX . $userId);
    }

    public static function isRunning(int $userId): bool
    {
        $status = self::getStatus($userId);

        return $status && $status['status'] === 'running';
    }
}
