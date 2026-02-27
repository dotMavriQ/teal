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

    public function render()
    {
        $perPage = $this->viewMode === 'list' ? 25 : 18;

        $query = Comic::query()
            ->where('user_id', Auth::id())
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->publisher, function ($query) {
                $query->where('publisher', $this->publisher);
            });

        if (in_array($this->sortBy, ['issue_count', 'start_year'])) {
            if ($this->sortDirection === 'asc') {
                $query->orderByRaw("{$this->sortBy} IS NOT NULL")
                    ->orderByRaw("CASE WHEN {$this->sortBy} IS NULL THEN title END ASC")
                    ->orderBy($this->sortBy, 'asc');
            } else {
                $query->orderByRaw("{$this->sortBy} IS NULL")
                    ->orderBy($this->sortBy, 'desc')
                    ->orderByRaw("CASE WHEN {$this->sortBy} IS NULL THEN title END DESC");
            }
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
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

            if (in_array($this->sortBy, ['issue_count', 'start_year'])) {
                if ($this->sortDirection === 'asc') {
                    $searchQuery->orderByRaw("{$this->sortBy} IS NOT NULL")
                        ->orderByRaw("CASE WHEN {$this->sortBy} IS NULL THEN title END ASC")
                        ->orderBy($this->sortBy, 'asc');
                } else {
                    $searchQuery->orderByRaw("{$this->sortBy} IS NULL")
                        ->orderBy($this->sortBy, 'desc')
                        ->orderByRaw("CASE WHEN {$this->sortBy} IS NULL THEN title END DESC");
                }
            } else {
                $searchQuery->orderBy($this->sortBy, $this->sortDirection);
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
