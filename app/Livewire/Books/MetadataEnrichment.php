<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\FetchBookMetadata;
use App\Models\Book;
use App\Services\OpenLibraryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MetadataEnrichment extends Component
{
    // Source priority: sources listed in order of preference
    public array $sourcePriority = ['current', 'openlibrary'];

    // Scanning state
    public array $booksNeedingEnrichment = [];

    public bool $hasScanned = false;

    public bool $isScanning = false;

    // Background job status
    public ?array $jobStatus = null;

    // Review modal (single book)
    public bool $showReviewModal = false;

    public ?int $reviewingBookId = null;

    public ?array $reviewingBook = null;

    public ?array $reviewingMetadata = null;

    public array $selectedFields = [];

    // Fetched data for single-book fetch (review modal)
    public array $fetchedData = [];

    public int $batchLimit = 100;

    public function mount(): void
    {
        // Check if there's an existing job running
        $this->refreshJobStatus();
    }

    public function refreshJobStatus(): void
    {
        $this->jobStatus = FetchBookMetadata::getStatus(Auth::id());
    }

    public function clearJobStatus(): void
    {
        FetchBookMetadata::clearStatus(Auth::id());
        $this->jobStatus = null;
    }

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

        // Find all books with ISBN that have missing metadata
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
                    'has_missing' => ! empty($missing),
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

    public function startBackgroundFetch(): void
    {
        if (FetchBookMetadata::isRunning(Auth::id())) {
            session()->flash('error', 'A metadata fetch is already running.');

            return;
        }

        // Get books that need enrichment (have missing fields)
        $booksToFetch = collect($this->booksNeedingEnrichment)
            ->filter(fn ($book) => $book['has_missing'])
            ->filter(fn ($book) => ! empty($book['isbn']))
            ->take($this->batchLimit)
            ->pluck('id')
            ->toArray();

        if (empty($booksToFetch)) {
            session()->flash('message', 'No books need metadata fetching.');

            return;
        }

        // Dispatch the job synchronously for immediate execution
        FetchBookMetadata::dispatch(
            Auth::id(),
            $booksToFetch,
            $this->sourcePriority
        );

        // Set initial status
        $this->jobStatus = [
            'status' => 'starting',
            'progress' => 0,
            'total' => count($booksToFetch),
            'fetched' => 0,
            'applied' => 0,
        ];

        session()->flash('message', 'Background fetch started for ' . count($booksToFetch) . ' books. You can leave this page - check back later for results.');
    }

    public function fetchSingleBook(int $id): void
    {
        $bookData = collect($this->booksNeedingEnrichment)->firstWhere('id', $id);

        if (! $bookData || empty($bookData['isbn'])) {
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

    public function startReview(int $id): void
    {
        $bookData = collect($this->booksNeedingEnrichment)->firstWhere('id', $id);

        if (! $bookData) {
            return;
        }

        $this->reviewingBookId = $id;
        $this->reviewingBook = $bookData;
        $this->reviewingMetadata = $this->fetchedData[$id] ?? null;

        // Pre-select fields based on priority
        $this->selectedFields = $this->getFieldsToApply($bookData, $this->reviewingMetadata);

        $this->showReviewModal = true;
    }

    protected function getFieldsToApply(array $bookData, ?array $metadata): array
    {
        if (! $metadata) {
            return [];
        }

        $fields = [];
        $currentFirst = $this->sourcePriority[0] === 'current';

        foreach (['description', 'publisher', 'page_count', 'published_date'] as $field) {
            $hasCurrentValue = ! empty($bookData['current'][$field]);
            $hasNewValue = ! empty($metadata[$field]);

            if (! $hasNewValue) {
                continue;
            }

            if (! $hasCurrentValue) {
                $fields[] = $field;
            } elseif (! $currentFirst) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    public function applyMetadata(): void
    {
        if (! $this->reviewingBookId || ! $this->reviewingMetadata || empty($this->selectedFields)) {
            $this->closeReviewModal();

            return;
        }

        $book = Book::query()
            ->where('user_id', Auth::id())
            ->find($this->reviewingBookId);

        if (! $book) {
            $this->closeReviewModal();

            return;
        }

        $updateData = [];

        foreach ($this->selectedFields as $field) {
            if (isset($this->reviewingMetadata[$field]) && $this->reviewingMetadata[$field] !== null) {
                $updateData[$field] = $this->reviewingMetadata[$field];
            }
        }

        if (! empty($updateData)) {
            $book->update($updateData);
        }

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

    protected function updateLocalBookData(int $bookId, array $updateData): void
    {
        foreach ($this->booksNeedingEnrichment as $index => $bookData) {
            if ($bookData['id'] === $bookId) {
                foreach ($updateData as $field => $value) {
                    $this->booksNeedingEnrichment[$index]['current'][$field] = $value;

                    $missingIndex = array_search($field, $this->booksNeedingEnrichment[$index]['missing']);
                    if ($missingIndex !== false) {
                        unset($this->booksNeedingEnrichment[$index]['missing'][$missingIndex]);
                        $this->booksNeedingEnrichment[$index]['missing'] = array_values($this->booksNeedingEnrichment[$index]['missing']);
                    }
                }

                $this->booksNeedingEnrichment[$index]['has_missing'] = ! empty($this->booksNeedingEnrichment[$index]['missing']);
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

    public function isJobRunning(): bool
    {
        return $this->jobStatus && $this->jobStatus['status'] === 'running';
    }

    public function isJobCompleted(): bool
    {
        return $this->jobStatus && $this->jobStatus['status'] === 'completed';
    }

    public function render()
    {
        // Auto-refresh job status if running
        if ($this->jobStatus && $this->jobStatus['status'] === 'running') {
            $this->refreshJobStatus();
        }

        return view('livewire.books.metadata-enrichment')
            ->layout('layouts.app');
    }
}
