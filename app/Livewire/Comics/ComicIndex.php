<?php

declare(strict_types=1);

namespace App\Livewire\Comics;

use App\Enums\ReadingStatus;
use App\Models\Comic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ComicIndex extends Component
{
    use WithPagination;

    private function normalizeForSearch(string $string): string
    {
        return Str::ascii($string);
    }

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

    public string $publisher = '';

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public string $viewMode = 'gallery';

    public array $selected = [];

    public bool $selectAll = false;

    private const ALLOWED_SORT_COLUMNS = ['title', 'rating', 'issue_count', 'start_year', 'date_finished', 'updated_at', 'publisher', 'date_started'];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'publisher' => ['except' => ''],
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

    public function updatingPublisher(): void
    {
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

    public function updateStatus(Comic $comic, string $status): void
    {
        $this->authorize('update', $comic);

        $comic->update([
            'status' => $status,
            'date_started' => $status === 'reading' && ! $comic->date_started ? now() : $comic->date_started,
            'date_finished' => $status === 'read' && ! $comic->date_finished ? now() : $comic->date_finished,
        ]);
    }

    public function deleteComic(Comic $comic): void
    {
        $this->authorize('delete', $comic);

        $comic->delete();

        session()->flash('message', 'Comic deleted successfully.');
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $query = Comic::query()
                ->where('user_id', Auth::id())
                ->when($this->status, function ($query) {
                    $query->where('status', $this->status);
                })
                ->when($this->publisher, function ($query) {
                    $query->where('publisher', $this->publisher);
                });

            if ($this->search) {
                $normalizedSearch = $this->normalizeForSearch($this->search);
                $allComics = $query->get();
                $this->selected = $allComics->filter(function ($comic) use ($normalizedSearch) {
                    return $this->matchesSearch($comic->title, $normalizedSearch)
                        || $this->matchesSearch($comic->publisher, $normalizedSearch);
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
        $count = Comic::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $this->selected)
            ->delete();

        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', "{$count} comic(s) deleted successfully.");
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

        $query = Comic::query()
            ->where('user_id', Auth::id())
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->publisher, function ($query) {
                $query->where('publisher', $this->publisher);
            });

        if (in_array($sortBy, ['issue_count', 'start_year'])) {
            if ($sortDir === 'asc') {
                $query->orderByRaw("{$sortBy} IS NOT NULL")
                    ->orderByRaw("CASE WHEN {$sortBy} IS NULL THEN title END ASC")
                    ->orderBy($sortBy, 'asc');
            } else {
                $query->orderByRaw("{$sortBy} IS NULL")
                    ->orderBy($sortBy, 'desc')
                    ->orderByRaw("CASE WHEN {$sortBy} IS NULL THEN title END DESC");
            }
        } elseif ($sortBy === 'date_finished') {
            $query->orderByRaw("COALESCE(date_finished, updated_at) {$sortDir}");
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        if ($this->search) {
            $normalizedSearch = $this->normalizeForSearch($this->search);

            $exactMatchIds = (clone $query)
                ->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('publisher', 'like', '%'.$this->search.'%');
                })
                ->pluck('id');

            $allComics = $query->get();
            $filteredIds = $allComics->filter(function ($comic) use ($normalizedSearch) {
                return $this->matchesSearch($comic->title, $normalizedSearch)
                    || $this->matchesSearch($comic->publisher, $normalizedSearch);
            })->pluck('id');

            $matchingIds = $exactMatchIds->merge($filteredIds)->unique();

            $searchQuery = Comic::query()
                ->whereIn('id', $matchingIds);

            if (in_array($sortBy, ['issue_count', 'start_year'])) {
                if ($sortDir === 'asc') {
                    $searchQuery->orderByRaw("{$sortBy} IS NOT NULL")
                        ->orderByRaw("CASE WHEN {$sortBy} IS NULL THEN title END ASC")
                        ->orderBy($sortBy, 'asc');
                } else {
                    $searchQuery->orderByRaw("{$sortBy} IS NULL")
                        ->orderBy($sortBy, 'desc')
                        ->orderByRaw("CASE WHEN {$sortBy} IS NULL THEN title END DESC");
                }
            } elseif ($sortBy === 'date_finished') {
                $searchQuery->orderByRaw("COALESCE(date_finished, updated_at) {$sortDir}");
            } else {
                $searchQuery->orderBy($sortBy, $sortDir);
            }

            $comics = $searchQuery->paginate($perPage);
        } else {
            $comics = $query->paginate($perPage);
        }

        $publishers = Comic::query()
            ->where('user_id', Auth::id())
            ->whereNotNull('publisher')
            ->distinct()
            ->orderBy('publisher')
            ->pluck('publisher');

        return view('livewire.comics.comic-index', [
            'comics' => $comics,
            'statuses' => ReadingStatus::cases(),
            'publishers' => $publishers,
        ])->layout('layouts.app');
    }
}
