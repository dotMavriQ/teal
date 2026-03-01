<?php

declare(strict_types=1);

namespace App\Livewire\Shows;

use App\Enums\WatchingStatus;
use App\Models\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ShowIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $genre = '';
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';
    public string $viewMode = 'gallery';
    public array $selected = [];
    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'genre' => ['except' => ''],
        'sortBy' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
        'viewMode' => ['except' => 'gallery'],
    ];

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
            mb_strtolower($this->normalizeForSearch($value)),
            $normalizedSearch
        );
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingGenre(): void
    {
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function deleteShow(Show $show): void
    {
        $this->authorize('delete', $show);
        $show->delete();
        session()->flash('message', 'Show deleted.');
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected = Show::forUser(Auth::user())
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function deleteSelected(): void
    {
        Show::whereIn('id', $this->selected)
            ->where('user_id', Auth::id())
            ->delete();

        $count = count($this->selected);
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('message', "{$count} shows deleted.");
    }

    public function getStatuses(): array
    {
        return WatchingStatus::cases();
    }

    public function paginationView(): string
    {
        return 'livewire.custom-pagination';
    }

    public function render()
    {
        $perPage = $this->viewMode === 'list' ? 25 : 18;

        $query = Show::forUser(Auth::user());

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->genre) {
            $query->where('genres', 'like', '%' . $this->genre . '%');
        }

        $sortColumn = match ($this->sortBy) {
            'title', 'year', 'rating', 'imdb_rating' => $this->sortBy,
            default => 'updated_at',
        };

        $query->orderBy($sortColumn, $this->sortDirection);

        $shows = $query->paginate($perPage);

        if ($this->search) {
            $normalizedSearch = mb_strtolower($this->normalizeForSearch($this->search));

            $shows->setCollection(
                $shows->getCollection()->filter(function ($show) use ($normalizedSearch) {
                    return $this->matchesSearch($show->title, $normalizedSearch)
                        || $this->matchesSearch($show->original_title, $normalizedSearch)
                        || $this->matchesSearch($show->genres, $normalizedSearch);
                })
            );
        }

        return view('livewire.shows.show-index', [
            'shows' => $shows,
            'statuses' => $this->getStatuses(),
            'allGenres' => Show::getAllGenresForUser(Auth::id()),
        ])->layout('layouts.app');
    }
}
