<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Enums\ReadingStatus;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class BookIndex extends Component
{
    use WithPagination;

    /**
     * Normalize a string by removing diacritics/accents for comparison.
     */
    private function normalizeForSearch(string $string): string
    {
        return Str::ascii($string);
    }

    /**
     * Check if a value matches the search term (accent-insensitive).
     */
    private function matchesSearch(?string $value, string $normalizedSearch): bool
    {
        if ($value === null) {
            return false;
        }

        return str_contains(
            strtolower($this->normalizeForSearch($value)),
            strtolower($normalizedSearch)
        );
    }

    public string $search = '';

    public string $status = '';

    public string $tag = '';

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public string $viewMode = 'gallery'; // gallery or list

    // Bulk delete
    public array $selected = [];

    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'tag' => ['except' => ''],
        'sortBy' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
        'viewMode' => ['except' => 'gallery'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingTag(): void
    {
        $this->resetPage();
    }

    public function clearTag(): void
    {
        $this->tag = '';
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['gallery', 'list']) ? $mode : 'gallery';
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updateStatus(Book $book, string $status): void
    {
        $this->authorize('update', $book);

        $book->update([
            'status' => $status,
            'date_started' => $status === 'reading' && ! $book->date_started ? now() : $book->date_started,
            'date_finished' => $status === 'read' && ! $book->date_finished ? now() : $book->date_finished,
        ]);
    }

    public function deleteBook(Book $book): void
    {
        $this->authorize('delete', $book);

        $book->delete();

        session()->flash('message', 'Book deleted successfully.');
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $query = Book::query()
                ->where('user_id', Auth::id())
                ->when($this->status, function ($query) {
                    $query->where('status', $this->status);
                })
                ->when($this->tag, function ($query) {
                    $query->where('shelves', 'like', '%'.$this->tag.'%');
                });

            if ($this->search) {
                $normalizedSearch = $this->normalizeForSearch($this->search);
                $allBooks = $query->get();
                $this->selected = $allBooks->filter(function ($book) use ($normalizedSearch) {
                    return $this->matchesSearch($book->title, $normalizedSearch)
                        || $this->matchesSearch($book->author, $normalizedSearch);
                })->pluck('id')->map(fn ($id) => (string) $id)->toArray();
            } else {
                $this->selected = $query->pluck('id')->map(fn ($id) => (string) $id)->toArray();
            }
        } else {
            $this->selected = [];
        }
    }

    public function deleteSelected(): void
    {
        $count = Book::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $this->selected)
            ->delete();

        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', "{$count} book(s) deleted successfully.");
    }

    public function getStatuses(): array
    {
        return ReadingStatus::cases();
    }

    public function render()
    {
        $perPage = $this->viewMode === 'list' ? 25 : 18;

        $query = Book::query()
            ->where('user_id', Auth::id())
            ->with('bookShelves')
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->tag, function ($query) {
                // Filter by tag in the shelves field (comma-separated)
                $query->where('shelves', 'like', '%'.$this->tag.'%');
            });

        // Special sorting for page_count: nulls first (sorted by title), then by page count
        if ($this->sortBy === 'page_count') {
            $query->orderByRaw('page_count IS NOT NULL')  // NULLs first
                ->orderByRaw('CASE WHEN page_count IS NULL THEN title END ASC')  // NULLs sorted by title A-Z
                ->orderBy('page_count', $this->sortDirection);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        // For search, use accent-insensitive PHP filtering
        if ($this->search) {
            $normalizedSearch = $this->normalizeForSearch($this->search);

            // First try exact match in SQL for performance
            $exactMatchIds = (clone $query)
                ->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('author', 'like', '%'.$this->search.'%');
                })
                ->pluck('id');

            // Then get all books and filter with accent-insensitive comparison
            $allBooks = $query->get();
            $filteredIds = $allBooks->filter(function ($book) use ($normalizedSearch) {
                return $this->matchesSearch($book->title, $normalizedSearch)
                    || $this->matchesSearch($book->author, $normalizedSearch);
            })->pluck('id');

            // Combine both result sets
            $matchingIds = $exactMatchIds->merge($filteredIds)->unique();

            $searchQuery = Book::query()
                ->whereIn('id', $matchingIds)
                ->with('bookShelves');

            if ($this->sortBy === 'page_count') {
                $searchQuery->orderByRaw('page_count IS NOT NULL')
                    ->orderByRaw('CASE WHEN page_count IS NULL THEN title END ASC')
                    ->orderBy('page_count', $this->sortDirection);
            } else {
                $searchQuery->orderBy($this->sortBy, $this->sortDirection);
            }

            $books = $searchQuery->paginate($perPage);
        } else {
            $books = $query->paginate($perPage);
        }

        return view('livewire.books.book-index', [
            'books' => $books,
            'statuses' => $this->getStatuses(),
            'allTags' => Book::getAllTagsForUser(Auth::id()),
        ])->layout('layouts.app');
    }
}
