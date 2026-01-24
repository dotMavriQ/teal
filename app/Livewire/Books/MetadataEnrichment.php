<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Models\Book;
use App\Services\OpenLibraryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MetadataEnrichment extends Component
{
    // Source priority: sources listed in order of preference
    // When there's a collision (both have values), the higher-priority source wins
    public array $sourcePriority = ['current', 'openlibrary'];

    // Scanning state
    public array $booksNeedingEnrichment = [];
    public bool $hasScanned = false;
    public bool $isScanning = false;

    // Fetched data from OpenLibrary
    public array $fetchedData = [];
    public bool $isFetching = false;
    public int $fetchProgress = 0;
    public int $fetchTotal = 0;

    // Review modal (single book)
    public bool $showReviewModal = false;
    public ?int $reviewingBookId = null;
    public ?array $reviewingBook = null;
    public ?array $reviewingMetadata = null;
    public array $selectedFields = [];

    public function moveSourceUp(string $source): void
    {
        $index = array_search($source, $this->sourcePriority);
        if ($index > 0) {
            $temp = $this->sourcePriority[$index - 1];
            $this->sourcePriority[$index - 1] = $source;
            $this->sourcePriority[$index] = $temp;
        }
    }

    public function moveSourceDown(string $source): void
    {
        $index = array_search($source, $this->sourcePriority);
        if ($index < count($this->sourcePriority) - 1) {
            $temp = $this->sourcePriority[$index + 1];
            $this->sourcePriority[$index + 1] = $source;
            $this->sourcePriority[$index] = $temp;
        }
    }

    public function scanLibrary(): void
    {
        $this->isScanning = true;
        $this->hasScanned = false;
        $this->fetchedData = [];

        // Find all books with ISBN that could potentially be enriched
        $this->booksNeedingEnrichment = Book::query()
            ->where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereNotNull('isbn')
                    ->orWhereNotNull('isbn13');
            })
            ->get(['id', 'title', 'author', 'isbn', 'isbn13', 'description', 'publisher', 'page_count', 'published_date'])
            ->map(function ($book) {
                $missing = $this->getMissingFields($book);
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'isbn' => $book->isbn13 ?? $book->isbn,
                    'current' => [
                        'description' => $book->description,
                        'publisher' => $book->publisher,
                        'page_count' => $book->page_count,
                        'published_date' => $book->published_date?->format('Y-m-d'),
                    ],
                    'missing' => $missing,
                    'has_missing' => !empty($missing),
                ];
            })
            ->toArray();

        $this->hasScanned = true;
        $this->isScanning = false;
    }

    protected function getMissingFields(Book $book): array
    {
        $missing = [];

        if (empty($book->description)) {
            $missing[] = 'description';
        }
        if (empty($book->publisher)) {
            $missing[] = 'publisher';
        }
        if (empty($book->page_count)) {
            $missing[] = 'page_count';
        }
        if (empty($book->published_date)) {
            $missing[] = 'published_date';
        }

        return $missing;
    }

    public function fetchAllMetadata(): void
    {
        if (empty($this->booksNeedingEnrichment)) {
            return;
        }

        $this->isFetching = true;
        $this->fetchedData = [];
        $this->fetchProgress = 0;
        $this->fetchTotal = count($this->booksNeedingEnrichment);

        $service = app(OpenLibraryService::class);

        foreach ($this->booksNeedingEnrichment as $index => $bookData) {
            $isbn = $bookData['isbn'];

            if (empty($isbn)) {
                $this->fetchProgress = $index + 1;
                continue;
            }

            $metadata = $service->fetchByIsbn($isbn);

            if ($metadata) {
                $this->fetchedData[$bookData['id']] = $metadata;
            }

            $this->fetchProgress = $index + 1;
        }

        $this->isFetching = false;
    }

    public function startReview(int $id): void
    {
        $bookData = collect($this->booksNeedingEnrichment)->firstWhere('id', $id);

        if (!$bookData) {
            return;
        }

        $this->reviewingBookId = $id;
        $this->reviewingBook = $bookData;
        $this->reviewingMetadata = $this->fetchedData[$id] ?? null;

        // Pre-select fields based on priority
        $this->selectedFields = $this->getFieldsToApply($bookData, $this->reviewingMetadata);

        $this->showReviewModal = true;
    }

    public function fetchSingleBook(int $id): void
    {
        $bookData = collect($this->booksNeedingEnrichment)->firstWhere('id', $id);

        if (!$bookData || empty($bookData['isbn'])) {
            return;
        }

        $service = app(OpenLibraryService::class);
        $metadata = $service->fetchByIsbn($bookData['isbn']);

        if ($metadata) {
            $this->fetchedData[$id] = $metadata;
            $this->reviewingMetadata = $metadata;
            $this->selectedFields = $this->getFieldsToApply($bookData, $metadata);
        }
    }

    protected function getFieldsToApply(array $bookData, ?array $metadata): array
    {
        if (!$metadata) {
            return [];
        }

        $fields = [];
        $currentFirst = $this->sourcePriority[0] === 'current';

        foreach (['description', 'publisher', 'page_count', 'published_date'] as $field) {
            $hasCurrentValue = !empty($bookData['current'][$field]);
            $hasNewValue = !empty($metadata[$field]);

            if (!$hasNewValue) {
                // No new value available, nothing to apply
                continue;
            }

            if (!$hasCurrentValue) {
                // Current is empty, always apply new value
                $fields[] = $field;
            } elseif (!$currentFirst) {
                // OpenLibrary has priority, overwrite existing
                $fields[] = $field;
            }
            // else: current has priority and has a value, don't overwrite
        }

        return $fields;
    }

    public function applyMetadata(): void
    {
        if (!$this->reviewingBookId || !$this->reviewingMetadata || empty($this->selectedFields)) {
            $this->closeReviewModal();
            return;
        }

        $book = Book::query()
            ->where('user_id', Auth::id())
            ->find($this->reviewingBookId);

        if (!$book) {
            $this->closeReviewModal();
            return;
        }

        $updateData = [];

        foreach ($this->selectedFields as $field) {
            if (isset($this->reviewingMetadata[$field]) && $this->reviewingMetadata[$field] !== null) {
                $updateData[$field] = $this->reviewingMetadata[$field];
            }
        }

        if (!empty($updateData)) {
            $book->update($updateData);
        }

        // Update local state
        $this->updateLocalBookData($this->reviewingBookId, $updateData);

        $this->closeReviewModal();

        session()->flash('message', 'Metadata applied successfully.');
    }

    public function skipBook(): void
    {
        $this->closeReviewModal();
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->reviewingBookId = null;
        $this->reviewingBook = null;
        $this->reviewingMetadata = null;
        $this->selectedFields = [];
    }

    public function bulkApply(): void
    {
        if (empty($this->fetchedData)) {
            session()->flash('error', 'No metadata fetched. Click "Fetch Metadata" first.');
            return;
        }

        $appliedCount = 0;

        foreach ($this->booksNeedingEnrichment as $bookData) {
            $bookId = $bookData['id'];
            $metadata = $this->fetchedData[$bookId] ?? null;

            if (!$metadata) {
                continue;
            }

            $fieldsToApply = $this->getFieldsToApply($bookData, $metadata);

            if (empty($fieldsToApply)) {
                continue;
            }

            $book = Book::query()
                ->where('user_id', Auth::id())
                ->find($bookId);

            if (!$book) {
                continue;
            }

            $updateData = [];

            foreach ($fieldsToApply as $field) {
                if (isset($metadata[$field]) && $metadata[$field] !== null) {
                    $updateData[$field] = $metadata[$field];
                }
            }

            if (!empty($updateData)) {
                $book->update($updateData);
                $this->updateLocalBookData($bookId, $updateData);
                $appliedCount++;
            }
        }

        session()->flash('message', "Metadata applied to {$appliedCount} book(s).");
    }

    protected function updateLocalBookData(int $bookId, array $updateData): void
    {
        foreach ($this->booksNeedingEnrichment as $index => $bookData) {
            if ($bookData['id'] === $bookId) {
                foreach ($updateData as $field => $value) {
                    $this->booksNeedingEnrichment[$index]['current'][$field] = $value;

                    // Remove from missing if it was there
                    $missingIndex = array_search($field, $this->booksNeedingEnrichment[$index]['missing']);
                    if ($missingIndex !== false) {
                        unset($this->booksNeedingEnrichment[$index]['missing'][$missingIndex]);
                        $this->booksNeedingEnrichment[$index]['missing'] = array_values($this->booksNeedingEnrichment[$index]['missing']);
                    }
                }

                $this->booksNeedingEnrichment[$index]['has_missing'] = !empty($this->booksNeedingEnrichment[$index]['missing']);
                break;
            }
        }
    }

    public function getSourceLabel(string $source): string
    {
        return match ($source) {
            'current' => 'Keep Current Values',
            'openlibrary' => 'OpenLibrary',
            default => $source,
        };
    }

    public function getBooksWithMissingCount(): int
    {
        return collect($this->booksNeedingEnrichment)->where('has_missing', true)->count();
    }

    public function getFetchedCount(): int
    {
        return count($this->fetchedData);
    }

    public function render()
    {
        return view('livewire.books.metadata-enrichment')
            ->layout('layouts.app');
    }
}
