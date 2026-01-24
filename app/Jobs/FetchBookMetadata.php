<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Book;
use App\Services\OpenLibraryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchBookMetadata implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 1800; // 30 minutes max

    private const CACHE_KEY_PREFIX = 'metadata_fetch_';

    public function __construct(
        public int $userId,
        public array $bookIds,
        public array $sourcePriority = ['current', 'openlibrary']
    ) {
        // No queue - runs via dispatchAfterResponse()
    }

    public function handle(): void
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $this->userId;

        // Mark as running
        Cache::put($cacheKey, [
            'status' => 'running',
            'progress' => 0,
            'total' => count($this->bookIds),
            'fetched' => 0,
            'applied' => 0,
            'started_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(2));

        $service = app(OpenLibraryService::class);
        $currentFirst = $this->sourcePriority[0] === 'current';

        $fetched = 0;
        $applied = 0;

        foreach ($this->bookIds as $index => $bookId) {
            try {
                $book = Book::where('user_id', $this->userId)->find($bookId);

                if (! $book) {
                    continue;
                }

                $isbn = $book->isbn13 ?? $book->isbn;

                if (empty($isbn)) {
                    continue;
                }

                // Fetch metadata from OpenLibrary
                $metadata = $service->fetchByIsbn($isbn);

                if ($metadata) {
                    $fetched++;

                    // Determine which fields to apply
                    $updateData = [];

                    foreach (['description', 'publisher', 'page_count', 'published_date'] as $field) {
                        $hasCurrentValue = ! empty($book->$field);
                        $hasNewValue = ! empty($metadata[$field]);

                        if (! $hasNewValue) {
                            continue;
                        }

                        if (! $hasCurrentValue) {
                            // Current is empty, apply new value
                            $updateData[$field] = $metadata[$field];
                        } elseif (! $currentFirst) {
                            // OpenLibrary has priority, overwrite existing
                            $updateData[$field] = $metadata[$field];
                        }
                    }

                    if (! empty($updateData)) {
                        $book->update($updateData);
                        $applied++;
                    }
                }

                // Update progress
                Cache::put($cacheKey, [
                    'status' => 'running',
                    'progress' => $index + 1,
                    'total' => count($this->bookIds),
                    'fetched' => $fetched,
                    'applied' => $applied,
                    'started_at' => Cache::get($cacheKey)['started_at'] ?? now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ], now()->addHours(2));

                // Small delay to avoid hammering the API
                usleep(250000); // 250ms

            } catch (\Exception $e) {
                Log::warning("FetchBookMetadata: Error fetching book {$bookId}: " . $e->getMessage());
            }
        }

        // Mark as completed
        Cache::put($cacheKey, [
            'status' => 'completed',
            'progress' => count($this->bookIds),
            'total' => count($this->bookIds),
            'fetched' => $fetched,
            'applied' => $applied,
            'started_at' => Cache::get($cacheKey)['started_at'] ?? now()->toIso8601String(),
            'completed_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(2));

        Log::info("FetchBookMetadata: Completed for user {$this->userId}. Fetched: {$fetched}, Applied: {$applied}");
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
