<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReadingStatus;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class JsonImportService
{
    public function parseJson(string $content): Collection
    {
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return collect();
        }

        return collect($data)
            ->filter()
            ->map(fn($item) => $this->mapJsonToBook($item));
    }

    protected function mapJsonToBook(array $item): array
    {
        $isbn = $this->cleanIsbn($item['isbn'] ?? '');
        $isbn13 = $this->cleanIsbn($item['isbn13'] ?? '');
        $asin = trim($item['asin'] ?? '');

        return [
            'title' => trim($item['title'] ?? ''),
            'author' => $this->parseAuthor($item['author'] ?? ''),
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'asin' => !empty($asin) ? $asin : null,
            'cover_url' => !empty($item['bookCover']) ? $item['bookCover'] : null,
            'page_count' => !empty($item['num_pages']) ? (int) $item['num_pages'] : null,
            'published_date' => $this->parseDate($item['date_pub'] ?? $item['date_pub__ed__'] ?? ''),
            'publisher' => null,
            'goodreads_id' => null,
            'status' => $this->mapShelfToStatus($item['shelves'] ?? ''),
            'rating' => $this->parseRating($item['rating'] ?? ''),
            'avg_rating' => !empty($item['avg_rating']) ? (float) $item['avg_rating'] : null,
            'num_ratings' => !empty($item['num_ratings']) ? $this->parseNumRatings($item['num_ratings']) : null,
            'date_pub' => $item['date_pub'] ?? null,
            'date_pub_edition' => $item['date_pub__ed__'] ?? null,
            'date_started' => $this->parseDate($item['date_started'] ?? ''),
            'date_finished' => $this->parseDate($item['date_read'] ?? ''),
            'date_added' => $this->parseDate($item['date_added'] ?? ''),
            'shelves' => !empty($item['shelves']) ? $item['shelves'] : null,
            'notes' => $item['notes'] ?? null,
            'review' => $item['review'] ?? null,
            'comments' => !empty($item['comments']) ? (int) $item['comments'] : null,
            'votes' => !empty($item['votes']) ? (int) $item['votes'] : null,
            'owned' => !empty($item['owned']) ? (bool) $item['owned'] : false,
        ];
    }

    protected function parseAuthor(string $author): ?string
    {
        $author = trim($author);

        return !empty($author) ? $author : null;
    }

    protected function cleanIsbn(string $isbn): ?string
    {
        $isbn = preg_replace('/[^0-9X]/i', '', $isbn);

        return !empty($isbn) ? $isbn : null;
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

    protected function parseRating($rating): ?int
    {
        if (empty($rating)) {
            return null;
        }

        $rating = (int) $rating;

        return $rating >= 1 && $rating <= 5 ? $rating : null;
    }

    protected function parseNumRatings($numRatings): ?int
    {
        $numRatings = (string) $numRatings;
        $numRatings = preg_replace('/[^0-9]/', '', $numRatings);

        return !empty($numRatings) ? (int) $numRatings : null;
    }

    protected function mapShelfToStatus(string $shelves): ReadingStatus
    {
        $shelves = strtolower(trim($shelves));

        // Check for 'read' keyword
        if (str_contains($shelves, 'read')) {
            return ReadingStatus::Read;
        }

        // Check for currently-reading
        if (str_contains($shelves, 'currently')) {
            return ReadingStatus::Reading;
        }

        return ReadingStatus::WantToRead;
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
                    $errors[] = "Item " . ($index + 1) . ": Missing title";
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
                $errors[] = "Item " . ($index + 1) . ": " . $e->getMessage();
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
        // Check by ISBN/ISBN13
        if (!empty($bookData['isbn13'])) {
            if (Book::where('user_id', $user->id)->where('isbn13', $bookData['isbn13'])->exists()) {
                return true;
            }
        }

        if (!empty($bookData['isbn'])) {
            if (Book::where('user_id', $user->id)->where('isbn', $bookData['isbn'])->exists()) {
                return true;
            }
        }

        // Check by ASIN if available
        if (!empty($bookData['asin'])) {
            if (Book::where('user_id', $user->id)->where('asin', $bookData['asin'])->exists()) {
                return true;
            }
        }

        // Check by title and author combination as last resort
        if (!empty($bookData['title'])) {
            $query = Book::where('user_id', $user->id)
                ->where('title', $bookData['title']);

            if (!empty($bookData['author'])) {
                $query->where('author', $bookData['author']);
            }

            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }
}
