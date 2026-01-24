<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Book;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FetchBookCover implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 420; // 7 minutes total (3 sources x 2 min + buffer)

    private const SOURCE_TIMEOUT = 120; // 2 minutes per source
    private const MIN_IMAGE_SIZE = 1000; // Reject tiny placeholder images (bytes)

    public function __construct(
        public int $bookId
    ) {
        $this->onQueue('covers');
    }

    public function handle(): void
    {
        $book = Book::find($this->bookId);

        if (!$book) {
            Log::warning("FetchBookCover: Book {$this->bookId} not found");
            return;
        }

        // Skip if cover already exists
        if ($book->cover_url) {
            return;
        }

        $isbn = $book->isbn13 ?: $book->isbn;

        if (empty($isbn)) {
            return;
        }

        $localPath = $this->downloadAndStoreCover($isbn, $book->id);

        if ($localPath) {
            $book->update(['cover_url' => $localPath]);
            Log::info("FetchBookCover: Saved cover for book {$book->id} ({$book->title})");
        } else {
            Log::info("FetchBookCover: No cover found for book {$book->id} ({$book->title})");
        }
    }

    private function downloadAndStoreCover(string $isbn, int $bookId): ?string
    {
        $sources = [
            "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg",
            "https://archive.org/services/img/bookcover?isbn={$isbn}",
            "https://bookcover.longitood.com/pageSource.php?isbn={$isbn}",
        ];

        foreach ($sources as $url) {
            $imageData = $this->fetchImage($url);

            if ($imageData) {
                return $this->storeImage($imageData, $bookId);
            }
        }

        return null;
    }

    private function fetchImage(string $url): ?string
    {
        try {
            $response = Http::timeout(self::SOURCE_TIMEOUT)
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $body = $response->body();

            // Reject tiny placeholder images
            if (strlen($body) < self::MIN_IMAGE_SIZE) {
                return null;
            }

            // Verify it's actually an image
            $contentType = $response->header('Content-Type');
            if ($contentType && !str_starts_with($contentType, 'image/')) {
                return null;
            }

            return $body;
        } catch (\Exception $e) {
            Log::debug("FetchBookCover: Error fetching {$url}: {$e->getMessage()}");
            return null;
        }
    }

    private function storeImage(string $imageData, int $bookId): ?string
    {
        try {
            // Detect image type from magic bytes
            $extension = $this->detectImageExtension($imageData);

            $filename = "covers/{$bookId}.{$extension}";

            Storage::disk('public')->put($filename, $imageData);

            // Return the public URL path
            return '/storage/' . $filename;
        } catch (\Exception $e) {
            Log::error("FetchBookCover: Error storing image for book {$bookId}: {$e->getMessage()}");
            return null;
        }
    }

    private function detectImageExtension(string $data): string
    {
        $magicBytes = substr($data, 0, 8);

        // JPEG
        if (str_starts_with($magicBytes, "\xFF\xD8\xFF")) {
            return 'jpg';
        }

        // PNG
        if (str_starts_with($magicBytes, "\x89PNG\r\n\x1a\n")) {
            return 'png';
        }

        // GIF
        if (str_starts_with($magicBytes, 'GIF87a') || str_starts_with($magicBytes, 'GIF89a')) {
            return 'gif';
        }

        // WebP
        if (substr($magicBytes, 0, 4) === 'RIFF' && substr($data, 8, 4) === 'WEBP') {
            return 'webp';
        }

        // Default to jpg
        return 'jpg';
    }
}
