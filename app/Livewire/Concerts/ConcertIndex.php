<?php

declare(strict_types=1);

namespace App\Livewire\Concerts;

use App\Enums\ListeningStatus;
use App\Livewire\Concerns\WithAccentInsensitiveSearch;
use App\Models\Concert;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ConcertIndex extends Component
{
    use WithAccentInsensitiveSearch;
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $sortBy = 'event_date';

    public string $sortDirection = 'desc';

    public string $viewMode = 'list';

    public bool $selectAll = false;

    public array $selected = [];

    private const ALLOWED_SORT_COLUMNS = [
        'artist', 'venue', 'city', 'event_date', 'rating', 'updated_at', 'created_at',
    ];

    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortBy' => ['except' => 'event_date'],
        'sortDirection' => ['except' => 'desc'],
        'viewMode' => ['except' => 'list'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['gallery', 'list']) ? $mode : 'list';
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
        return in_array($this->sortBy, self::ALLOWED_SORT_COLUMNS, true) ? $this->sortBy : 'event_date';
    }

    public function deleteConcert(Concert $concert): void
    {
        $this->authorize('delete', $concert);

        $concert->delete();

        session()->flash('message', 'Concert deleted successfully.');
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
        Concert::whereIn('id', $this->selected)
            ->where('user_id', Auth::id())
            ->delete();

        $count = count($this->selected);
        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', "{$count} concert(s) deleted.");
    }

    private function buildQuery()
    {
        $query = Concert::where('user_id', Auth::id());

        if ($this->search !== '') {
            $query = $this->applyAccentInsensitiveSearch($query, $this->search, ['artist', 'venue', 'city', 'tour_name']);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function render()
    {
        $perPage = $this->viewMode === 'list' ? 25 : 18;
        $sortBy = $this->safeSortBy();
        $sortDir = $this->safeSortDirection();

        $query = $this->buildQuery();

        if (in_array($sortBy, ['rating', 'event_date'])) {
            $query->orderByRaw("\"$sortBy\" $sortDir NULLS LAST");
        } else {
            $query->orderBy($sortBy, $sortDir);
        }
        $query->orderBy('id');

        $concerts = $query->paginate($perPage);

        return view('livewire.concerts.concert-index', [
            'concerts' => $concerts,
            'statuses' => ListeningStatus::cases(),
        ])->layout('layouts.app');
    }
}
