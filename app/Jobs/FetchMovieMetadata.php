<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Movie;
use App\Services\TmdbService;
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

        $service = app(TmdbService::class);
        $currentFirst = $this->sourcePriority[0] === 'current';

        $fetched = 0;
        $applied = 0;

        foreach ($this->movieIds as $index => $movieId) {
            try {
                $movie = Movie::where('user_id', $this->userId)->find($movieId);

                if (! $movie) {
                    continue;
                }

                // Skip episodes — they clutter TMDB results
                if (stripos($movie->title, 'episode') !== false) {
                    continue;
                }

                // Try by IMDb ID first, then by title+year
                $metadata = null;

                if (! empty($movie->imdb_id)) {
                    $metadata = $service->findByImdbId($movie->imdb_id);
                }

                if (! $metadata && ! empty($movie->title)) {
                    $metadata = $service->searchByTitle($movie->title, $movie->year);
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
