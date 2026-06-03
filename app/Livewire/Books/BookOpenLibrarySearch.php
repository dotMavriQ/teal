<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Enums\ReadingStatus;
use App\Models\Book;
use App\Services\GoogleBooksService;
use App\Services\OpenLibraryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookOpenLibrarySearch extends Component
{
    public string $step = 'search';

    // Search state
    public string $query = '';

    public string $searchSource = 'google_books';

    /** @var list<array<string, mixed>> */
    public array $searchResults = [];

    public int $totalPages = 0;

    public int $currentPage = 1;

    // Selected book configuration
    public string $title = '';

    public string $author = '';

    public ?string $isbn = null;

    public ?int $page_count = null;

    public string $description = '';

    public string $cover_url = '';

    public ?string $publisher = null;

    public ?int $published_year = null;

    public string $status = 'want_to_read';

    public ?int $rating = null;

    // Duplicate detection
    /** @var array<array-key, mixed> */
    public array $existingIsbns = [];

    public function mount(): void
    {
        $userId = Auth::id();

        $this->existingIsbns = Book::where('user_id', $userId)
            ->whereNotNull('isbn')
            ->pluck('isbn')
            ->merge(
                Book::where('user_id', $userId)
                    ->whereNotNull('isbn13')
                    ->pluck('isbn13')
            )
            ->all();
    }

    public function search(): void
    {
        $query = trim($this->query);
        if ($query === '') {
            return;
        }

        $result = $this->searchWithSource($query, 1);

        $this->searchResults = $result['results'];
        $this->totalPages = min($result['total_pages'], 50);
        $this->currentPage = 1;
        $this->step = 'results';
    }

    public function loadPage(int $page): void
    {
        $result = $this->searchWithSource(trim($this->query), $page);

        $this->searchResults = $result['results'];
        $this->currentPage = $page;
    }

    /**
     * @return array{results: list<array<string, mixed>>, total: int, total_pages: int}
     */
    protected function searchWithSource(string $query, int $page): array
    {
        if ($this->searchSource === 'google_books') {
            return app(GoogleBooksService::class)->search($query, $page);
        }

        return app(OpenLibraryService::class)->search($query, $page);
    }

    public function selectResult(int $index): void
    {
        $result = $this->searchResults[$index] ?? null;
        if (! $result) {
            return;
        }

        $this->title = $this->strOf($result['title'] ?? null);
        $this->author = $this->strOf($result['author'] ?? null);
        $this->isbn = $this->strOrNull($result['isbn'] ?? null);
        $this->page_count = $this->intOrNull($result['page_count'] ?? null);
        $this->cover_url = $this->strOf($result['cover_url_large'] ?? $result['cover_url'] ?? null);
        $this->publisher = $this->strOrNull($result['publisher'] ?? null);
        $this->published_year = $this->intOrNull($result['first_publish_year'] ?? null);
        $this->description = $this->strOf($result['description'] ?? null);
        $this->status = 'want_to_read';
        $this->rating = null;

        // If no description from search results, try ISBN lookup (OpenLibrary)
        if (empty($this->description) && $this->isbn) {
            $service = app(OpenLibraryService::class);
            $details = $service->fetchByIsbn($this->isbn);
            if ($details) {
                $this->description = $this->strOf($details['description'] ?? null);
                $this->page_count = $this->page_count ?? $this->intOrNull($details['page_count'] ?? null);
                $this->publisher = $this->publisher ?? $this->strOrNull($details['publisher'] ?? null);
            }
        }

        $this->step = 'configure';
    }

    private function strOf(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function strOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    public function addBook(): void
    {
        $publishedDate = null;
        if ($this->published_year) {
            $publishedDate = $this->published_year.'-01-01';
        }

        $book = Book::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'author' => $this->author ?: null,
            'isbn' => $this->isbn ?: null,
            'cover_url' => $this->cover_url ?: null,
            'description' => $this->description ?: null,
            'page_count' => $this->page_count,
            'publisher' => $this->publisher ?: null,
            'published_date' => $publishedDate,
            'status' => $this->status,
            'rating' => $this->rating,
            'date_added' => now(),
        ]);

        session()->flash('message', "Added \"{$this->title}\" to your library.");
        $this->redirect(route('books.show', $book));
    }

    public function backToSearch(): void
    {
        $this->step = 'search';
        $this->searchResults = [];
        $this->query = '';
    }

    public function backToResults(): void
    {
        $this->step = 'results';
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function isResultDuplicate(array $result): bool
    {
        $isbn = $result['isbn'] ?? null;

        return $isbn && in_array($isbn, $this->existingIsbns);
    }

    public function render()
    {
        return view('livewire.books.book-openlibrary-search', [
            'statuses' => ReadingStatus::cases(),
        ])->layout('layouts.app');
    }
}
