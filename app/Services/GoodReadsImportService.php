<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReadingStatus;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class GoodReadsImportService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function parseCSV(string $content): Collection
    {
        $lines = explode("\n", $content);
        $headers = array_map(fn ($h): string => (string) $h, str_getcsv(array_shift($lines)));

        /** @var Collection<int, array<string, mixed>> $books */
        $books = collect();

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line);

            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, array_map(fn ($v): ?string => $v ?? null, $row));

            $books->push($this->mapRowToBook($data));
        }

        return $books;
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    protected function mapRowToBook(array $row): array
    {
        $isbn = $this->cleanIsbn($row['ISBN'] ?? '');
        $isbn13 = $this->cleanIsbn($row['ISBN13'] ?? '');

        return [
            'title' => $row['Title'] ?? '',
            'author' => $this->parseAuthor($row['Author'] ?? '', $row['Additional Authors'] ?? ''),
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'page_count' => empty($row['Number of Pages']) ? null : (int) $row['Number of Pages'],
            'published_date' => $this->parseYear($row['Year Published'] ?? $row['Original Publication Year'] ?? ''),
            'publisher' => $row['Publisher'] ?? null,
            'goodreads_id' => $row['Book Id'] ?? $row['Book ID'] ?? null,
            'status' => $this->mapShelfToStatus($row['Exclusive Shelf'] ?? $row['Shelves'] ?? ''),
            'rating' => $this->parseRating($row['My Rating'] ?? ''),
            'date_started' => $this->parseDate($row['Date Started'] ?? ''),
            'date_finished' => $this->parseDate($row['Date Read'] ?? ''),
            'notes' => $row['My Review'] ?? $row['Review'] ?? null,
            'cover_url' => null,
        ];
    }

    protected function parseAuthor(string $author, string $additionalAuthors): ?string
    {
        $authors = array_filter([trim($author), trim($additionalAuthors)]);

        return $authors === [] ? null : implode(', ', $authors);
    }

    protected function cleanIsbn(string $isbn): ?string
    {
        $isbn = preg_replace('/[^0-9X]/i', '', $isbn) ?? '';

        return $isbn !== '' ? $isbn : null;
    }

    protected function parseYear(?string $year): ?string
    {
        if (empty($year) || ! is_numeric($year)) {
            return null;
        }

        return $year.'-01-01';
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

    protected function parseRating(?string $rating): ?int
    {
        if (empty($rating)) {
            return null;
        }

        $rating = (int) $rating;

        return $rating >= 1 && $rating <= 5 ? $rating : null;
    }

    protected function mapShelfToStatus(string $shelf): ReadingStatus
    {
        return match (strtolower(trim($shelf))) {
            'currently-reading' => ReadingStatus::Reading,
            'read' => ReadingStatus::Read,
            default => ReadingStatus::WantToRead,
        };
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
        $existingIds = ['goodreads' => [], 'isbn13' => [], 'isbn' => []];
        if ($skipDuplicates) {
            $userBooks = Book::where('user_id', $user->id)
                ->select('goodreads_id', 'isbn13', 'isbn')
                ->get();
            $existingIds['goodreads'] = $userBooks->pluck('goodreads_id')->filter()->flip()->all();
            $existingIds['isbn13'] = $userBooks->pluck('isbn13')->filter()->flip()->all();
            $existingIds['isbn'] = $userBooks->pluck('isbn')->filter()->flip()->all();
        }

        foreach ($books as $index => $bookData) {
            try {
                if (empty($bookData['title'])) {
                    $errors[] = 'Row '.($index + 2).': Missing title';

                    continue;
                }

                if ($skipDuplicates && $this->isDuplicateFromCache($bookData, $existingIds)) {
                    $skipped++;

                    continue;
                }

                $status = $bookData['status'] ?? null;
                $bookData['status'] = $status instanceof ReadingStatus ? $status->value : $status;
                $bookData['user_id'] = $user->id;

                $book = Book::create($bookData);
                $bookIds[] = $book->id;
                $imported++;

                // Update cache with newly imported book
                if ($skipDuplicates) {
                    foreach (['goodreads' => 'goodreads_id', 'isbn13' => 'isbn13', 'isbn' => 'isbn'] as $cache => $field) {
                        $value = $bookData[$field] ?? null;
                        if (! empty($value) && (is_int($value) || is_string($value))) {
                            $existingIds[$cache][$value] = true;
                        }
                    }
                }
            } catch (Exception $e) {
                $errors[] = 'Row '.($index + 2).': '.$e->getMessage();
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
        foreach (['goodreads' => 'goodreads_id', 'isbn13' => 'isbn13', 'isbn' => 'isbn'] as $cache => $field) {
            $value = $bookData[$field] ?? null;
            if (! empty($value) && (is_int($value) || is_string($value)) && isset($existingIds[$cache][$value])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $bookData
     */
    protected function isDuplicate(User $user, array $bookData): bool
    {
        $query = Book::where('user_id', $user->id);

        foreach (['goodreads_id', 'isbn13', 'isbn'] as $field) {
            $value = $bookData[$field] ?? null;
            if (! empty($value) && $query->clone()->where($field, $value)->exists()) {
                return true;
            }
        }

        return false;
    }
}
