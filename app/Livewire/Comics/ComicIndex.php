<?php

declare(strict_types=1);

namespace App\Livewire\Comics;

use App\Enums\ReadingStatus;
use App\Livewire\Concerns\WithAccentInsensitiveSearch;
use App\Livewire\Concerns\WithIndexFiltering;
use App\Models\Comic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ComicIndex extends Component
{
    use WithAccentInsensitiveSearch;
    use WithIndexFiltering;
    use WithPagination;

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

    public function updatingStatus(string $value): void
    {
        if ($value === 'want_to_read' && in_array($this->sortBy, ['date_finished', 'date_started'])) {
            $this->sortBy = 'updated_at';
        } elseif ($value === 'reading' && $this->sortBy === 'date_finished') {
            $this->sortBy = 'date_started';
        }

        $this->resetPage();
    }

    public function updatingPublisher(): void
    {
        $this->resetPage();
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
                $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'publisher']);
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
            $query->orderBy(DB::raw('COALESCE(date_finished, updated_at)'), $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $query->orderBy('id');

        if ($this->search) {
            $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'publisher']);
        }

        $comics = $query->paginate($perPage);

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
