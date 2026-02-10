<?php

declare(strict_types=1);

namespace App\Livewire\Anime;

use App\Enums\WatchingStatus;
use App\Models\Anime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class AnimeIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $genre = '';

    public string $mediaType = '';

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public string $viewMode = 'gallery';

    public array $selected = [];

    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'genre' => ['except' => ''],
        'mediaType' => ['except' => ''],
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
            strtolower($this->normalizeForSearch($value)),
            strtolower($normalizedSearch)
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

    public function updatingMediaType(): void
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

    public function deleteAnime(Anime $anime): void
    {
        $this->authorize('delete', $anime);

        $anime->delete();

        session()->flash('message', 'Anime deleted successfully.');
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $query = Anime::query()
                ->where('user_id', Auth::id())
                ->when($this->status, function ($query) {
                    $query->where('status', $this->status);
                })
                ->when($this->genre, function ($query) {
                    $query->where('genres', 'like', '%' . $this->genre . '%');
                })
                ->when($this->mediaType, function ($query) {
                    $query->where('media_type', $this->mediaType);
                });

            if ($this->search) {
                $normalizedSearch = $this->normalizeForSearch($this->search);
                $allAnime = $query->get();
                $this->selected = $allAnime->filter(function ($anime) use ($normalizedSearch) {
                    return $this->matchesSearch($anime->title, $normalizedSearch)
                        || $this->matchesSearch($anime->original_title, $normalizedSearch)
                        || $this->matchesSearch($anime->studios, $normalizedSearch);
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
        $count = Anime::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $this->selected)
            ->delete();

        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', "{$count} anime deleted successfully.");
    }

    public function getStatuses(): array
    {
        return WatchingStatus::cases();
    }

    public function render()
    {
        $perPage = $this->viewMode === 'list' ? 25 : 18;

        $query = Anime::query()
            ->where('user_id', Auth::id())
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->genre, function ($query) {
                $query->where('genres', 'like', '%' . $this->genre . '%');
            })
            ->when($this->mediaType, function ($query) {
                $query->where('media_type', $this->mediaType);
            });

        $query->orderBy($this->sortBy, $this->sortDirection);

        if ($this->search) {
            $normalizedSearch = $this->normalizeForSearch($this->search);

            $exactMatchIds = (clone $query)
                ->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('original_title', 'like', '%' . $this->search . '%')
                        ->orWhere('studios', 'like', '%' . $this->search . '%');
                })
                ->pluck('id');

            $allAnime = $query->get();
            $filteredIds = $allAnime->filter(function ($anime) use ($normalizedSearch) {
                return $this->matchesSearch($anime->title, $normalizedSearch)
                    || $this->matchesSearch($anime->original_title, $normalizedSearch)
                    || $this->matchesSearch($anime->studios, $normalizedSearch);
            })->pluck('id');

            $matchingIds = $exactMatchIds->merge($filteredIds)->unique();

            $searchQuery = Anime::query()
                ->whereIn('id', $matchingIds)
                ->orderBy($this->sortBy, $this->sortDirection);

            $animeList = $searchQuery->paginate($perPage);
        } else {
            $animeList = $query->paginate($perPage);
        }

        $allMediaTypes = Anime::where('user_id', Auth::id())
            ->whereNotNull('media_type')
            ->distinct()
            ->pluck('media_type')
            ->sort()
            ->values();

        return view('livewire.anime.anime-index', [
            'animeList' => $animeList,
            'statuses' => $this->getStatuses(),
            'allGenres' => Anime::getAllGenresForUser(Auth::id()),
            'allMediaTypes' => $allMediaTypes,
        ])->layout('layouts.app');
    }
}
