<?php

declare(strict_types=1);

namespace App\Livewire\Albums;

use App\Enums\CollectionStatus;
use App\Livewire\Concerns\WithAccentInsensitiveSearch;
use App\Livewire\Concerns\WithIndexFiltering;
use App\Models\Album;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AlbumIndex extends Component
{
    use WithAccentInsensitiveSearch;
    use WithIndexFiltering;
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public string $viewMode = 'gallery';

    public bool $selectAll = false;

    public array $selected = [];

    private const ALLOWED_SORT_COLUMNS = [
        'title', 'artist', 'year', 'rating', 'updated_at', 'created_at',
    ];

    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
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


    public function deleteAlbum(Album $album): void
    {
        $this->authorize('delete', $album);

        $album->delete();

        session()->flash('message', 'Album deleted successfully.');
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
        Album::whereIn('id', $this->selected)
            ->where('user_id', Auth::id())
            ->delete();

        $count = count($this->selected);
        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', "{$count} album(s) deleted.");
    }

    private function buildQuery()
    {
        $query = Album::where('user_id', Auth::id());

        if ($this->search !== '') {
            $query = $this->applyAccentInsensitiveSearch($query, $this->search, ['title', 'artist', 'label']);
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

        if (in_array($sortBy, ['rating', 'year'])) {
            $query->orderByRaw("\"$sortBy\" $sortDir NULLS LAST");
        } else {
            $query->orderBy($sortBy, $sortDir);
        }
        $query->orderBy('id');

        $albums = $query->paginate($perPage);

        return view('livewire.albums.album-index', [
            'albums' => $albums,
            'statuses' => CollectionStatus::cases(),
        ])->layout('layouts.app');
    }
}
