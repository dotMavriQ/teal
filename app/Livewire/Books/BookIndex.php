<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Enums\ReadingStatus;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BookIndex extends Component
{
    use WithPagination;

    private function applyAccentInsensitiveSearch($query, string $search, array $columns): void
    {
        $words = preg_split('/\s+/', trim($search));

        foreach ($words as $word) {
            $query->where(function ($q) use ($word, $columns) {
                foreach ($columns as $column) {
                    $q->orWhereRaw('unaccent(COALESCE(' . $column . ", '')) ILIKE unaccent(?)", ['%' . $word . '%']);
                }
            });
        }
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

    private const ALLOWED_SORT_COLUMNS = ['title', 'author', 'rating', 'page_count', 'date_finished', 'updated_at', 'date_started', 'date_recorded'];

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

    private function safeSortDirection(): string
    {
        return $this->sortDirection === 'asc' ? 'asc' : 'desc';
    }

    private function safeSortBy(): string
    {
        return in_array($this->sortBy, self::ALLOWED_SORT_COLUMNS, true) ? $this->sortBy : 'updated_at';
    }

    public function updateStatus(Book $book, string $status): void
    {
        $this->authorize('update', $book);

        $book->update([
            'status' => $status,
            'date_started' => $status === 'reading' && ! $book->date_started ? now() : $book->date_started,
            'date_finished' => $status === 'read' && ! $book->date_finished ? now() : $book->date_finished,
        ]);

        // Auto-remove from queue when marked as read
        if ($status === 'read' && $book->queue_position !== null) {
            $oldPosition = $book->queue_position;
            $book->update(['queue_position' => null]);

            Book::where('user_id', Auth::id())
                ->where('queue_position', '>', $oldPosition)
                ->decrement('queue_position');
        }
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
                    if ($this->tag === '__untagged__') {
                        $query->where(function ($q) {
                            $q->whereNull('shelves')
                              ->orWhere('shelves', '')
                              ->orWhereRaw("TRIM(shelves) IN ('read', 'to-read', 'currently-reading', 'want-to-read')");
                        });
                    } else {
                        $query->where('shelves', 'like', '%'.$this->tag.'%');
                    }
                });

            if ($this->search) {
                $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'author']);
                $this->selected = $query->pluck('id')->map(fn ($id) => (string) $id)->toArray();
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

    public function paginationView(): string
    {
        return 'livewire.custom-pagination';
    }

    public function render()
    {
        $perPage = $this->viewMode === 'list' ? 25 : 18;
        $sortBy = $this->safeSortBy();
        $sortDir = $this->safeSortDirection();

        $query = Book::query()
            ->where('user_id', Auth::id())
            ->with('bookShelves')
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->tag, function ($query) {
                if ($this->tag === '__untagged__') {
                    // Filter for books with no tags
                    $query->where(function ($q) {
                        $q->whereNull('shelves')
                          ->orWhere('shelves', '')
                          ->orWhereRaw("TRIM(shelves) IN ('read', 'to-read', 'currently-reading', 'want-to-read')");
                    });
                } else {
                    // Filter by tag in the shelves field (comma-separated)
                    $query->where('shelves', 'like', '%'.$this->tag.'%');
                }
            });

        // Special sorting for page_count: NULLs treated as "lower than 0"
        // ASC: NULLs first (A-Z by title), then page counts ascending
        // DESC: Page counts descending, then NULLs last (A-Z by title)
        if ($sortBy === 'page_count') {
            if ($sortDir === 'asc') {
                $query->orderByRaw('page_count IS NOT NULL')  // NULLs first
                    ->orderByRaw('CASE WHEN page_count IS NULL THEN title END ASC')  // NULLs sorted by title A-Z
                    ->orderBy('page_count', 'asc');
            } else {
                $query->orderByRaw('page_count IS NULL')  // NULLs last
                    ->orderBy('page_count', 'desc')
                    ->orderByRaw('CASE WHEN page_count IS NULL THEN title END DESC');  // NULLs sorted by title Z-A
            }
        } elseif ($sortBy === 'date_finished') {
            $query->orderBy(\Illuminate\Support\Facades\DB::raw('COALESCE(date_finished, updated_at)'), $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        if ($this->search) {
            $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'author']);
        }

        $books = $query->paginate($perPage);

        return view('livewire.books.book-index', [
            'books' => $books,
            'statuses' => $this->getStatuses(),
            'allTags' => Book::getAllTagsForUser(Auth::id()),
        ])->layout('layouts.app');
    }
}
