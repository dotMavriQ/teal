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

    private const MAX_DIMENSION = 600; // Max width/height after resize (pixels)

    private const COMPRESSION_QUALITY = 75; // JPEG quality 0-100

    public function __construct(
        public int $bookId,
        public ?string $externalCoverUrl = null
    ) {
        // No queue - runs after response via dispatchAfterResponse()
    }

    public function handle(): void
    {
        $book = Book::find($this->bookId);

        if (! $book) {
            Log::warning("FetchBookCover: Book {$this->bookId} not found");

            return;
        }

        // Skip if we already have a local cover
        if ($book->cover_url && str_starts_with($book->cover_url, '/storage/')) {
            return;
        }

        // Try external URL first (from import or already stored)
        $externalUrl = $this->externalCoverUrl ?? $book->cover_url;
        if ($externalUrl && filter_var($externalUrl, FILTER_VALIDATE_URL)) {
            $localPath = $this->downloadFromExternalUrl($externalUrl, $book->id);

            if ($localPath) {
                $book->update(['cover_url' => $localPath]);
                Log::info("FetchBookCover: Saved cover for book {$book->id} ({$book->title}) from external URL");

                return;
            }
        }

        // Fall back to ISBN-based search
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

    private function downloadFromExternalUrl(string $url, int $bookId): ?string
    {
        try {
            // Validate URL
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                return null;
            }

            $imageData = $this->fetchImage($url);

            if ($imageData) {
                return $this->storeImage($imageData, $bookId);
            }

            return null;
        } catch (\Exception $e) {
            Log::debug("FetchBookCover: Error processing external URL for book {$bookId}: {$e->getMessage()}");

            return null;
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

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();

            // Reject tiny placeholder images
            if (strlen($body) < self::MIN_IMAGE_SIZE) {
                return null;
            }

            // Verify it's actually an image
            $contentType = $response->header('Content-Type');
            if ($contentType && ! str_starts_with($contentType, 'image/')) {
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

            // Compress and optimize image
            $optimizedData = $this->optimizeImage($imageData, $extension);

            $filename = "covers/{$bookId}.{$extension}";

            Storage::disk('public')->put($filename, $optimizedData);

            // Return the public URL path
            return '/storage/'.$filename;
        } catch (\Exception $e) {
            Log::error("FetchBookCover: Error storing image for book {$bookId}: {$e->getMessage()}");

            return null;
        }
    }

    private function optimizeImage(string $imageData, string $extension): string
    {
        try {
            // Try to use Intervention Image v3 if available
            if (class_exists(\Intervention\Image\ImageManager::class) &&
                class_exists(\Intervention\Image\Drivers\Gd\Driver::class)) {
                return $this->optimizeWithIntervention($imageData, $extension);
            }
        } catch (\Exception $e) {
            Log::debug("FetchBookCover: Intervention Image optimization failed: {$e->getMessage()}");
        }

        // If Intervention Image is not available, return original
        return $imageData;
    }

    private function optimizeWithIntervention(string $imageData, string $extension): string
    {
        // Intervention Image v3 API
        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver
        );
        $image = $manager->read($imageData);

        // Resize to reasonable dimensions if too large
        if ($image->width() > self::MAX_DIMENSION || $image->height() > self::MAX_DIMENSION) {
            $image->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION);
        }

        // Encode with compression
        if ($extension === 'webp') {
            return (string) $image->toWebp(self::COMPRESSION_QUALITY);
        } elseif ($extension === 'png') {
            return (string) $image->toPng();
        } else {
            // JPEG or fallback
            return (string) $image->toJpeg(self::COMPRESSION_QUALITY);
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
