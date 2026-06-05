<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReadingStatus;
use App\Models\Book;
use App\Models\Shelf;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class JsonImportService
{
    /** @var list<string> */
    private array $statusKeywords = ['read', 'to-read', 'currently-reading', 'want-to-read', 'reading'];

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function parseJson(string $content): Collection
    {
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON: '.json_last_error_msg());
        }

        if (! is_array($data)) {
            throw new InvalidArgumentException('Invalid format: JSON must be an array of books');
        }

        return collect($data)
            ->filter()
            ->map(fn ($item): array => $this->mapJsonToBook(is_array($item) ? $item : []))
            ->values();
    }

    /**
     * @param  array<array-key, mixed>  $item
     * @return array<string, mixed>
     */
    protected function mapJsonToBook(array $item): array
    {
        $isbn = $this->cleanIsbn($this->strOf($item['isbn'] ?? null));
        $isbn13 = $this->cleanIsbn($this->strOf($item['isbn13'] ?? null));
        $asin = trim($this->strOf($item['asin'] ?? null));

        return [
            'title' => trim($this->strOf($item['title'] ?? null)),
            'author' => $this->parseAuthor($this->strOf($item['author'] ?? null)),
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'asin' => $asin !== '' ? $asin : null,
            'cover_url' => empty($item['bookCover']) ? null : $item['bookCover'],
            'page_count' => $this->toIntOrNull($item['num_pages'] ?? null) ?: null,
            'published_date' => $this->parseDate($this->strOf($item['date_pub'] ?? $item['date_pub__ed__'] ?? null)),
            'publisher' => null,
            'goodreads_id' => null,
            'status' => $this->mapShelfToStatus($this->strOf($item['shelves'] ?? null)),
            'rating' => $this->parseRating($item['rating'] ?? null),
            'avg_rating' => $this->toFloatOrNull($item['avg_rating'] ?? null) ?: null,
            'num_ratings' => empty($item['num_ratings']) ? null : $this->parseNumRatings($item['num_ratings']),
            'date_pub' => $item['date_pub'] ?? null,
            'date_pub_edition' => $item['date_pub__ed__'] ?? null,
            'date_started' => $this->parseDate($this->strOf($item['date_started'] ?? null)),
            'date_finished' => $this->parseDate($this->strOf($item['date_read'] ?? null)),
            'date_added' => $this->parseDate($this->strOf($item['date_added'] ?? null)),
            'shelves' => empty($item['shelves']) ? null : $item['shelves'],
            'notes' => $item['notes'] ?? null,
            'review' => $item['review'] ?? null,
            'comments' => $this->toIntOrNull($item['comments'] ?? null) ?: null,
            'votes' => $this->toIntOrNull($item['votes'] ?? null) ?: null,
            'owned' => ! empty($item['owned']),
        ];
    }

    protected function parseAuthor(string $author): ?string
    {
        $author = trim($author);

        return $author !== '' ? $author : null;
    }

    protected function cleanIsbn(string $isbn): ?string
    {
        $isbn = preg_replace('/[^0-9X]/i', '', $isbn) ?? '';

        return $isbn !== '' ? $isbn : null;
    }

    protected function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (Exception) {
            return null;
        }
    }

    protected function parseRating(mixed $rating): ?int
    {
        if (empty($rating)) {
            return null;
        }

        $rating = is_numeric($rating) ? (int) $rating : 0;

        return $rating >= 1 && $rating <= 5 ? $rating : null;
    }

    protected function parseNumRatings(mixed $numRatings): ?int
    {
        $numRatings = preg_replace('/[^0-9]/', '', $this->strOf($numRatings)) ?? '';

        return $numRatings !== '' ? (int) $numRatings : null;
    }

    protected function mapShelfToStatus(string $shelves): ReadingStatus
    {
        $shelves = strtolower(trim($shelves));

        // Check for "want to read" / "to-read" FIRST (before checking for "read")
        if (str_contains($shelves, 'to-read') || str_contains($shelves, 'want')) {
            return ReadingStatus::WantToRead;
        }

        // Check for currently-reading
        if (str_contains($shelves, 'currently') || str_contains($shelves, 'reading')) {
            return ReadingStatus::Reading;
        }

        // Check for 'read' (finished)
        if (str_contains($shelves, 'read')) {
            return ReadingStatus::Read;
        }

        // Default to want to read
        return ReadingStatus::WantToRead;
    }

    /**
     * @return list<string>
     */
    protected function extractCustomShelves(string $shelves): array
    {
        $parts = array_map(trim(...), explode(',', $shelves));

        // Skip the first part (status) and filter out status keywords
        $customShelves = [];
        $counter = count($parts);
        for ($i = 1; $i < $counter; $i++) {
            $shelfName = trim($parts[$i]);
            $shelfLower = strtolower($shelfName);

            if (! empty($shelfName) && ! in_array($shelfLower, $this->statusKeywords, true)) {
                $customShelves[] = $shelfName;
            }
        }

        return $customShelves;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $books
     * @return array{imported: int, skipped: int, errors: list<string>, book_ids: list<int>}
     */
    public function importBooks(User $user, Collection $books, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $bookIds = [];

        // Pre-load existing identifiers for batch duplicate detection
        $existingIds = ['isbn13' => [], 'isbn' => [], 'asin' => [], 'title_author' => []];
        if ($skipDuplicates) {
            $userBooks = Book::where('user_id', $user->id)
                ->select('isbn13', 'isbn', 'asin', 'title', 'author')
                ->get();
            $existingIds['isbn13'] = $userBooks->pluck('isbn13')->filter()->flip()->all();
            $existingIds['isbn'] = $userBooks->pluck('isbn')->filter()->flip()->all();
            $existingIds['asin'] = $userBooks->pluck('asin')->filter()->flip()->all();
            $existingIds['title_author'] = $userBooks->map(fn ($b): string => strtolower($b->title.'|'.($b->author ?? '')))->flip()->all();
        }

        foreach ($books as $index => $bookData) {
            try {
                if (empty($bookData['title'])) {
                    $errors[] = 'Item '.($index + 1).': Missing title';

                    continue;
                }

                if ($skipDuplicates && $this->isDuplicateFromCache($bookData, $existingIds)) {
                    $skipped++;

                    continue;
                }

                // Extract custom shelves before creating book
                $shelvesValue = $bookData['shelves'] ?? null;
                $customShelves = is_string($shelvesValue) && $shelvesValue !== ''
                    ? $this->extractCustomShelves($shelvesValue)
                    : [];

                $status = $bookData['status'] ?? null;
                $bookData['status'] = $status instanceof ReadingStatus ? $status->value : $status;
                $bookData['user_id'] = $user->id;

                $book = Book::create($bookData);
                $bookIds[] = $book->id;

                // Attach custom shelves
                if ($customShelves !== []) {
                    $shelfIds = [];
                    foreach ($customShelves as $shelfName) {
                        $shelf = Shelf::findOrCreateForUser($user->id, $shelfName);
                        $shelfIds[] = $shelf->id;
                    }
                    $book->bookShelves()->attach($shelfIds);
                }

                // Update cache with newly imported book
                if ($skipDuplicates) {
                    foreach (['isbn13', 'isbn', 'asin'] as $field) {
                        $value = $bookData[$field] ?? null;
                        if (! empty($value) && (is_int($value) || is_string($value))) {
                            $existingIds[$field][$value] = true;
                        }
                    }
                    $existingIds['title_author'][$this->titleAuthorKey($bookData)] = true;
                }

                $imported++;
            } catch (Exception $e) {
                $errors[] = 'Item '.($index + 1).': '.$e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'book_ids' => $bookIds,
        ];
    }

    /**
     * @param  array<string, mixed>  $bookData
     * @param  array<string, array<array-key, mixed>>  $existingIds
     */
    protected function isDuplicateFromCache(array $bookData, array $existingIds): bool
    {
        foreach (['isbn13', 'isbn', 'asin'] as $field) {
            $value = $bookData[$field] ?? null;
            if (! empty($value) && (is_int($value) || is_string($value)) && isset($existingIds[$field][$value])) {
                return true;
            }
        }

        if (! empty($bookData['title'])) {
            return isset($existingIds['title_author'][$this->titleAuthorKey($bookData)]);
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $bookData
     */
    protected function titleAuthorKey(array $bookData): string
    {
        return strtolower($this->strOf($bookData['title'] ?? null).'|'.$this->strOf($bookData['author'] ?? null));
    }

    /**
     * @param  array<string, mixed>  $bookData
     */
    protected function isDuplicate(User $user, array $bookData): bool
    {
        foreach (['isbn13', 'isbn', 'asin'] as $field) {
            $value = $bookData[$field] ?? null;
            if (! empty($value) && Book::where('user_id', $user->id)->where($field, $value)->exists()) {
                return true;
            }
        }

        // Check by title and author combination as last resort
        if (! empty($bookData['title'])) {
            $query = Book::where('user_id', $user->id)
                ->where('title', $bookData['title']);

            if (! empty($bookData['author'])) {
                $query->where('author', $bookData['author']);
            }

            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }

    protected function toIntOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    protected function toFloatOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    protected function strOf(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
