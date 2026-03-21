<?php

declare(strict_types=1);

namespace App\Livewire\Games;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use App\Livewire\Concerns\WithAccentInsensitiveSearch;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class GameIndex extends Component
{
    use WithAccentInsensitiveSearch;
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $ownership = '';

    public string $platform = '';

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
        if ($value === 'want_to_play' && in_array($this->sortBy, ['date_finished', 'date_started'])) {
            $this->sortBy = 'updated_at';
        } elseif ($value === 'playing' && $this->sortBy === 'date_finished') {
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
            });

        if ($this->search) {
            $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'developer', 'publisher']);
        }

        return $query;
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

        $query = $this->buildQuery();
        $query->orderBy($sortBy, $sortDir)->orderBy('id');

        $games = $query->paginate($perPage);

        $allPlatforms = Game::where('user_id', Auth::id())
            ->whereNotNull('platform')
            ->pluck('platform')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('livewire.games.game-index', [
            'games' => $games,
            'statuses' => PlayingStatus::cases(),
            'ownershipStatuses' => OwnershipStatus::cases(),
            'allPlatforms' => $allPlatforms,
        ])->layout('layouts.app');
    }
}
