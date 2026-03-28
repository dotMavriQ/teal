<?php

declare(strict_types=1);

namespace App\Livewire\BoardGames;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use App\Livewire\Concerns\WithAccentInsensitiveSearch;
use App\Livewire\Concerns\WithIndexFiltering;
use App\Models\BoardGame;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BoardGameIndex extends Component
{
    use WithAccentInsensitiveSearch;
    use WithIndexFiltering;
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $ownership = '';

    public string $genre = '';

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public string $viewMode = 'gallery';

    public bool $selectAll = false;

    public array $selected = [];

    private const ALLOWED_SORT_COLUMNS = [
        'title', 'rating', 'year_published', 'plays',
        'date_started', 'date_finished', 'updated_at', 'created_at',
    ];

    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'ownership' => ['except' => ''],
        'genre' => ['except' => ''],
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

    public function updatingOwnership(): void
    {
        $this->resetPage();
    }

    public function updatingGenre(): void
    {
        $this->resetPage();
    }


    public function deleteBoardGame(BoardGame $boardGame): void
    {
        $this->authorize('delete', $boardGame);

        $boardGame->delete();

        session()->flash('message', 'Board game deleted successfully.');
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
        BoardGame::whereIn('id', $this->selected)
            ->where('user_id', Auth::id())
            ->delete();

        $count = count($this->selected);
        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', "{$count} board game(s) deleted.");
    }

    private function buildQuery()
    {
        $query = BoardGame::where('user_id', Auth::id());

        if ($this->search !== '') {
            $query = $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'designer', 'publisher']);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->ownership !== '') {
            $query->where('ownership', $this->ownership);
        }

        if ($this->genre !== '') {
            $query->whereJsonContains('genre', $this->genre);
        }

        return $query;
    }

    public function render()
    {
        $perPage = $this->viewMode === 'list' ? 25 : 18;
        $sortBy = $this->safeSortBy();
        $sortDir = $this->safeSortDirection();

        $query = $this->buildQuery();

        if (in_array($sortBy, ['rating', 'plays', 'date_started', 'date_finished', 'year_published'])) {
            $query->orderByRaw("\"$sortBy\" $sortDir NULLS LAST");
        } else {
            $query->orderBy($sortBy, $sortDir);
        }
        $query->orderBy('id');

        $boardGames = $query->paginate($perPage);

        $allGenres = BoardGame::where('user_id', Auth::id())
            ->whereNotNull('genre')
            ->pluck('genre')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('livewire.board-games.board-game-index', [
            'boardGames' => $boardGames,
            'statuses' => PlayingStatus::cases(),
            'ownershipStatuses' => OwnershipStatus::cases(),
            'allGenres' => $allGenres,
        ])->layout('layouts.app');
    }
}
