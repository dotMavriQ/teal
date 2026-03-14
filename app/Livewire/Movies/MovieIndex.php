<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Enums\WatchingStatus;
use App\Models\Movie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MovieIndex extends Component
{
    use WithPagination;

    private const TV_SHOW_TYPES = ['TV Episode', 'TV Series', 'TV Mini Series'];

    public string $search = '';

    public string $status = '';

    public string $genre = '';

    public string $typeFilter = '';

    public bool $hideEpisodes = false;

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public string $viewMode = 'gallery';

    public array $selected = [];

    public bool $selectAll = false;

    private const ALLOWED_SORT_COLUMNS = ['title', 'rating', 'runtime_minutes', 'year', 'date_watched', 'updated_at', 'imdb_rating', 'release_date'];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'genre' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'hideEpisodes' => ['except' => false],
        'sortBy' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
        'viewMode' => ['except' => 'gallery'],
    ];

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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(string $value): void
    {
        if ($value !== '' && $value !== 'watched' && $this->sortBy === 'date_watched') {
            $this->sortBy = 'updated_at';
        }

        $this->resetPage();
    }

    public function updatingGenre(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function toggleHideEpisodes(): void
    {
        $this->hideEpisodes = ! $this->hideEpisodes;
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

    public function deleteMovie(Movie $movie): void
    {
        $this->authorize('delete', $movie);

        $movie->delete();

        session()->flash('message', 'Movie deleted successfully.');
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $query = Movie::query()
                ->where('user_id', Auth::id())
                ->when($this->status, function ($query) {
                    $query->where('status', $this->status);
                })
                ->when($this->genre, function ($query) {
                    $query->where('genres', 'like', '%' . $this->genre . '%');
                });

            $this->applyTypeFilter($query);

            if ($this->search) {
                $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'director', 'original_title']);
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
        $count = Movie::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $this->selected)
            ->delete();

        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', "{$count} movie(s) deleted successfully.");
    }

    private function applyTypeFilter($query): void
    {
        if ($this->typeFilter === 'tv_shows') {
            $query->whereIn('title_type', self::TV_SHOW_TYPES);
        } elseif ($this->typeFilter !== '') {
            $query->where('title_type', $this->typeFilter);
        }

        if ($this->hideEpisodes) {
            $query->where(function ($q) {
                $q->where('title_type', '!=', 'TV Episode')
                    ->orWhereNull('title_type');
            })->whereNull('season_number');
        }
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
        $sortBy = $this->safeSortBy();
        $sortDir = $this->safeSortDirection();

        $query = Movie::query()
            ->where('user_id', Auth::id())
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->genre, function ($query) {
                $query->where('genres', 'like', '%' . $this->genre . '%');
            });

        $this->applyTypeFilter($query);

        if ($this->search) {
            $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'director', 'original_title']);
        }

        if ($sortBy === 'runtime_minutes') {
            if ($sortDir === 'asc') {
                $query->orderByRaw('runtime_minutes IS NOT NULL')
                    ->orderByRaw('CASE WHEN runtime_minutes IS NULL THEN title END ASC')
                    ->orderBy('runtime_minutes', 'asc');
            } else {
                $query->orderByRaw('runtime_minutes IS NULL')
                    ->orderBy('runtime_minutes', 'desc')
                    ->orderByRaw('CASE WHEN runtime_minutes IS NULL THEN title END DESC');
            }
        } elseif ($sortBy === 'year') {
            $query->orderByRaw('year IS NULL')
                ->orderBy('year', $sortDir);
        } elseif ($sortBy === 'date_watched') {
            // Prioritize status 'watched' over others, then use date_watched, falling back to date_added/updated_at
            if ($sortDir === 'desc') {
                $query->orderByRaw("CASE WHEN status = 'watched' THEN 0 ELSE 1 END")
                    ->orderByRaw('COALESCE(date_watched, date_added, updated_at) DESC');
            } else {
                $query->orderByRaw("CASE WHEN status = 'watched' THEN 0 ELSE 1 END")
                    ->orderByRaw('COALESCE(date_watched, date_added, updated_at) ASC');
            }
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $movies = $query->paginate($perPage);

        $rawTypes = Movie::where('user_id', Auth::id())
            ->whereNotNull('title_type')
            ->distinct()
            ->pluck('title_type');

        // Build curated type list: "TV Shows" replaces the grouped TV types,
        // inserted alphabetically among the other types (first in the TV block)
        $hasTvShows = $rawTypes->intersect(self::TV_SHOW_TYPES)->isNotEmpty();
        $otherTypes = $rawTypes->reject(fn ($t) => in_array($t, self::TV_SHOW_TYPES))->sort()->values();

        $allTypes = collect();
        $tvShowsInserted = false;
        foreach ($otherTypes as $type) {
            // Insert "TV Shows" right before the first item that sorts after it
            if ($hasTvShows && ! $tvShowsInserted && strcasecmp($type, 'TV Shows') > 0) {
                $allTypes->push(['value' => 'tv_shows', 'label' => 'TV Shows']);
                $tvShowsInserted = true;
            }
            $allTypes->push(['value' => $type, 'label' => $type]);
        }
        // If TV Shows hasn't been inserted yet (sorts last), append it
        if ($hasTvShows && ! $tvShowsInserted) {
            $allTypes->push(['value' => 'tv_shows', 'label' => 'TV Shows']);
        }

        return view('livewire.movies.movie-index', [
            'movies' => $movies,
            'statuses' => $this->getStatuses(),
            'allGenres' => Movie::getAllGenresForUser(Auth::id()),
            'allTypes' => $allTypes,
        ])->layout('layouts.app');
    }
}
