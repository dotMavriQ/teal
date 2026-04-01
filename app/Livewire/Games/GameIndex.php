<?php

declare(strict_types=1);

namespace App\Livewire\Games;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use App\Livewire\Concerns\WithAccentInsensitiveSearch;
use App\Livewire\Concerns\WithIndexFiltering;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class GameIndex extends Component
{
    use WithAccentInsensitiveSearch;
    use WithIndexFiltering;
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $ownership = '';

    public string $platform = '';

    public string $genre = '';

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public string $viewMode = 'gallery';

    public array $selected = [];

    public bool $selectAll = false;

    private const ALLOWED_SORT_COLUMNS = ['title', 'rating', 'release_date', 'hours_played', 'completion_percentage', 'date_started', 'date_finished', 'updated_at'];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'ownership' => ['except' => ''],
        'platform' => ['except' => ''],
        'genre' => ['except' => ''],
        'sortBy' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
        'viewMode' => ['except' => 'gallery'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(string $value): void
    {
        if ($value === 'backlog' && in_array($this->sortBy, ['date_finished', 'date_started'])) {
            $this->sortBy = 'updated_at';
        } elseif (in_array($value, ['playing', 'shelved']) && $this->sortBy === 'date_finished') {
            $this->sortBy = 'date_started';
        }

        $this->resetPage();
    }

    public function updatingOwnership(): void
    {
        $this->resetPage();
    }

    public function updatingPlatform(): void
    {
        $this->resetPage();
    }

    public function updatingGenre(): void
    {
        $this->resetPage();
    }


    public function deleteGame(Game $game): void
    {
        $this->authorize('delete', $game);

        $game->delete();

        session()->flash('message', 'Game deleted successfully.');
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $query = $this->buildQuery();
            $this->selected = $query->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function deleteSelected(): void
    {
        $count = Game::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $this->selected)
            ->delete();

        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', "{$count} game(s) deleted successfully.");
    }

    protected function buildQuery()
    {
        $query = Game::query()
            ->where('user_id', Auth::id())
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->ownership, function ($query) {
                $query->where('ownership', $this->ownership);
            })
            ->when($this->platform, function ($query) {
                $query->whereJsonContains('platform', $this->platform);
            })
            ->when($this->genre, function ($query) {
                $query->whereJsonContains('genre', $this->genre);
            });

        if ($this->search) {
            $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'developer', 'publisher']);
        }

        return $query;
    }

    public function render()
    {
        $perPage = $this->viewMode === 'list' ? 25 : 18;
        $sortBy = $this->safeSortBy();
        $sortDir = $this->safeSortDirection();

        $query = $this->buildQuery();
        if (in_array($sortBy, ['rating', 'hours_played', 'completion_percentage', 'date_started', 'date_finished', 'release_date'])) {
            $query->orderByRaw("\"$sortBy\" $sortDir NULLS LAST");
        } else {
            $query->orderBy($sortBy, $sortDir);
        }
        $query->orderBy('id');

        $games = $query->paginate($perPage);

        $allPlatforms = Game::where('user_id', Auth::id())
            ->whereNotNull('platform')
            ->pluck('platform')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        $allGenres = Game::where('user_id', Auth::id())
            ->whereNotNull('genre')
            ->pluck('genre')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('livewire.games.game-index', [
            'games' => $games,
            'statuses' => PlayingStatus::cases(),
            'ownershipStatuses' => OwnershipStatus::cases(),
            'allPlatforms' => $allPlatforms,
            'allGenres' => $allGenres,
        ])->layout('layouts.app');
    }
}
