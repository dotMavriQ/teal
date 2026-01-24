<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\JsonImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportFromJson implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600; // 10 minutes

    public function __construct(
        public int $userId,
        public string $jsonContent,
        public bool $skipDuplicates = true,
    ) {
        $this->onQueue('imports');
        $this->delay(random_int(5, 30)); // Random delay between 5-30 seconds
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::error("ImportFromJson: User {$this->userId} not found");

            return;
        }

        try {
            $service = new JsonImportService;
            $books = $service->parseJson($this->jsonContent);

            if ($books->isEmpty()) {
                Log::warning("ImportFromJson: No valid books found in JSON for user {$user->id}");

                return;
            }

            $result = $service->importBooks($user, $books, $this->skipDuplicates);

            Log::info("ImportFromJson: Imported {$result['imported']} books for user {$user->id}", [
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'errors' => count($result['errors']),
            ]);

            if (! empty($result['book_ids'])) {
                $this->dispatchCoverFetchJobs($result['book_ids']);
            }

            if (! empty($result['errors'])) {
                Log::warning('ImportFromJson: Errors during import', [
                    'errors' => $result['errors'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error("ImportFromJson: Error importing books for user {$user->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    protected function dispatchCoverFetchJobs(array $bookIds): void
    {
        foreach ($bookIds as $bookId) {
            $book = \App\Models\Book::find($bookId);

            // Only dispatch for books with ISBN or direct cover URL
            if ($book && (($book->isbn || $book->isbn13) || $book->cover_url)) {
                // Add random delay to each job to spread network requests
                FetchBookCover::dispatch($bookId)
                    ->delay(random_int(10, 120)); // 10-120 seconds random delay
            }
        }
    }
}
