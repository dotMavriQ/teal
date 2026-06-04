<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\FetchBookMetadata;
use App\Livewire\Concerns\WithMetadataEnrichment;
use App\Livewire\Concerns\WithSourcePriority;
use App\Models\Book;
use App\Services\OpenLibraryService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MetadataEnrichment extends Component
{
    use WithMetadataEnrichment;
    use WithSourcePriority;

    // Source priority: sources listed in order of preference
    /** @var list<string> */
    public array $sourcePriority = ['current', 'openlibrary'];

    // Scanning state
    /** @var array<int, array<string, mixed>> */
    public array $booksNeedingEnrichment = [];

    public bool $hasScanned = false;

    public bool $isScanning = false;

    // Background job status
    /** @var array<array-key, mixed>|null */
    public ?array $jobStatus = null;

    // Review modal (single book)
    public bool $showReviewModal = false;

    public ?int $reviewingBookId = null;

    /** @var array<string, mixed>|null */
    public ?array $reviewingBook = null;

    /** @var array<string, mixed>|null */
    public ?array $reviewingMetadata = null;

    /** @var list<string> */
    public array $selectedFields = [];

    // Fetched data for single-book fetch (review modal)
    /** @var array<int, array<string, mixed>> */
    public array $fetchedData = [];

    public int $batchLimit = 100;

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function enrichmentList(): array
    {
        return $this->booksNeedingEnrichment;
    }

    /**
     * @param  array<int, array<string, mixed>>  $list
     */
    protected function setEnrichmentList(array $list): void
    {
        $this->booksNeedingEnrichment = $list;
    }

    protected function setReviewingId(?int $id): void
    {
        $this->reviewingBookId = $id;
    }

    /**
     * @param  array<string, mixed>|null  $item
     */
    protected function setReviewingItem(?array $item): void
    {
        $this->reviewingBook = $item;
    }

    /**
     * @return list<string>
     */
    protected function enrichableFields(): array
    {
        return ['description', 'publisher', 'page_count', 'published_date'];
    }

    /**
     * @param  array<array-key, mixed>|null  $data
     */
    private function intFrom(?array $data, string $key): int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function strFrom(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    public function mount(): void
    {
        // Check if there's an existing job running
        $this->refreshJobStatus();
    }

    public function refreshJobStatus(): void
    {
        $this->jobStatus = FetchBookMetadata::getStatus((int) Auth::id());
    }

    public function clearJobStatus(): void
    {
        FetchBookMetadata::clearStatus((int) Auth::id());
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
            ->where(function ($query): void {
                $query->whereNotNull('isbn')
                    ->orWhereNotNull('isbn13');
            })
            ->get(['id', 'title', 'author', 'isbn', 'isbn13', 'description', 'publisher', 'page_count', 'published_date'])
            ->map(function (Book $book): array {
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
                    'has_missing' => $missing !== [],
                ];
            })
            ->all();

        $this->hasScanned = true;
        $this->isScanning = false;
    }

    /**
     * @return list<string>
     */
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
        if (FetchBookMetadata::isRunning((int) Auth::id())) {
            session()->flash('error', 'A metadata fetch is already running.');

            return;
        }

        // Get books that need enrichment (have missing fields)
        $booksToFetch = collect($this->booksNeedingEnrichment)
            ->filter(fn ($book): bool => (bool) $book['has_missing'])
            ->filter(fn ($book): bool => ! empty($book['isbn']))
            ->take($this->batchLimit)
            ->pluck('id')
            ->all();

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
            (int) Auth::id(),
            array_values(array_map(fn ($id): int => is_numeric($id) ? (int) $id : 0, $booksToFetch)),
            $this->sourcePriority
        );
        $job->handle();

        // Refresh status after job completes
        $this->refreshJobStatus();

        // Show completion message
        $fetched = $this->intFrom($this->jobStatus, 'fetched');
        $applied = $this->intFrom($this->jobStatus, 'applied');
        session()->flash('message', "Metadata fetch completed! Updated {$applied} of {$fetched} books.");
    }

    public function fetchSingleBook(int $id): void
    {
        $bookData = collect($this->booksNeedingEnrichment)->firstWhere('id', $id);

        if (! is_array($bookData)) {
            return;
        }

        $isbn = $this->strFrom($bookData, 'isbn');

        if ($isbn === '') {
            return;
        }

        $service = app(OpenLibraryService::class);
        $metadata = $service->fetchByIsbn($isbn);

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
        if (! $this->reviewingBookId || ! $this->reviewingMetadata || $this->selectedFields === []) {
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

        if ($updateData !== []) {
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

    #[Layout('layouts.app')]
    public function render(): View
    {
        // Auto-refresh job status if running
        if ($this->jobStatus && $this->jobStatus['status'] === 'running') {
            $this->refreshJobStatus();
        }

        return view('livewire.books.metadata-enrichment');
    }
}
