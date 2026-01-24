<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReadingStatus;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GoodReadsImportService
{
    public function parseCSV(string $content): Collection
    {
        $lines = explode("\n", $content);
        $headers = str_getcsv(array_shift($lines));

        $books = collect();

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line);

            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, $row);

            $books->push($this->mapRowToBook($data));
        }

        return $books;
    }

    protected function mapRowToBook(array $row): array
    {
        $isbn = $this->cleanIsbn($row['ISBN'] ?? '');
        $isbn13 = $this->cleanIsbn($row['ISBN13'] ?? '');

        return [
            'title' => $row['Title'] ?? '',
            'author' => $this->parseAuthor($row['Author'] ?? '', $row['Additional Authors'] ?? ''),
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'page_count' => !empty($row['Number of Pages']) ? (int) $row['Number of Pages'] : null,
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

        return !empty($authors) ? implode(', ', $authors) : null;
    }

    protected function cleanIsbn(string $isbn): ?string
    {
        $isbn = preg_replace('/[^0-9X]/i', '', $isbn);

        return !empty($isbn) ? $isbn : null;
    }

    protected function parseYear(?string $year): ?string
    {
        if (empty($year) || !is_numeric($year)) {
            return null;
        }

        return $year . '-01-01';
    }

    protected function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    protected function parseRating(?string $rating): ?int
    {
        if (empty($rating) || $rating === '0') {
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

    public function importBooks(User $user, Collection $books, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $bookIds = [];

        foreach ($books as $index => $bookData) {
            try {
                if (empty($bookData['title'])) {
                    $errors[] = "Row " . ($index + 2) . ": Missing title";
                    continue;
                }

                if ($skipDuplicates && $this->isDuplicate($user, $bookData)) {
                    $skipped++;
                    continue;
                }

                $bookData['user_id'] = $user->id;
                $bookData['status'] = $bookData['status']->value;

                $book = Book::create($bookData);
                $bookIds[] = $book->id;
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'book_ids' => $bookIds,
        ];
    }

    protected function isDuplicate(User $user, array $bookData): bool
    {
        $query = Book::where('user_id', $user->id);

        if (!empty($bookData['goodreads_id'])) {
            if ($query->clone()->where('goodreads_id', $bookData['goodreads_id'])->exists()) {
                return true;
            }
        }

        if (!empty($bookData['isbn13'])) {
            if ($query->clone()->where('isbn13', $bookData['isbn13'])->exists()) {
                return true;
            }
        }

        if (!empty($bookData['isbn'])) {
            if ($query->clone()->where('isbn', $bookData['isbn'])->exists()) {
                return true;
            }
        }

        return false;
    }
}
