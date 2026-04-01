<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\FetchBookMetadata;
use App\Models\Book;
use App\Services\OpenLibraryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class MetadataEnrichment extends Component
{
    use \App\Livewire\Concerns\WithMetadataEnrichment;
    use \App\Livewire\Concerns\WithSourcePriority;

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

    protected function enrichmentListProperty(): string
    {
        return 'booksNeedingEnrichment';
    }

    protected function reviewingIdProperty(): string
    {
        return 'reviewingBookId';
    }

    protected function reviewingItemProperty(): string
    {
        return 'reviewingBook';
    }

    protected function enrichableFields(): array
    {
        return ['description', 'publisher', 'page_count', 'published_date'];
    }

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

        // Set initial status BEFORE executing
        $initialStatus = [
            'status' => 'running',
            'progress' => 0,
            'total' => count($booksToFetch),
            'fetched' => 0,
            'applied' => 0,
            'started_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];
        Cache::put('metadata_fetch_'.Auth::id(), $initialStatus, now()->addHours(2));

        $this->jobStatus = $initialStatus;

        // Execute the job synchronously for immediate feedback
        $job = new FetchBookMetadata(
            Auth::id(),
            $booksToFetch,
            $this->sourcePriority
        );
        $job->handle();

        // Refresh status after job completes
        $this->refreshJobStatus();

        // Show completion message
        $fetched = $this->jobStatus['fetched'] ?? 0;
        $applied = $this->jobStatus['applied'] ?? 0;
        session()->flash('message', "Metadata fetch completed! Updated {$applied} of {$fetched} books.");
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
        $this->openReviewFor($id);
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

        $updateData = $this->buildUpdateData();

        if (! empty($updateData)) {
            $book->update($updateData);
        }

        $this->updateLocalItemData($this->reviewingBookId, $updateData);

        $this->closeReviewModal();

        session()->flash('message', 'Metadata applied successfully.');
    }

    public function skipBook(): void
    {
        $this->closeReviewModal();
    }

    public function getSourceLabel(string $source): string
    {
        return match ($source) {
            'current' => 'Keep Current Values',
            'openlibrary' => 'OpenLibrary',
            default => $source,
        };
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
