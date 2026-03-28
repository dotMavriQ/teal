<?php

declare(strict_types=1);

namespace App\Livewire\Albums;

use App\Enums\CollectionStatus;
use App\Enums\OwnershipStatus;
use App\Models\Album;
use App\Services\DiscogsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AlbumDiscogsSearch extends Component
{
    public string $step = 'search';

    public string $searchQuery = '';

    public array $results = [];

    public ?array $selectedRelease = null;

    public string $status = 'wishlist';

    public string $ownership = 'not_owned';

    public ?int $rating = null;

    public string $notes = '';

    public function search(): void
    {
        if (empty(trim($this->searchQuery))) {
            return;
        }

        $service = app(DiscogsService::class);
        $this->results = $service->search($this->searchQuery);
        $this->step = 'results';
    }

    public function selectRelease(int $index): void
    {
        $result = $this->results[$index] ?? null;

        if (! $result) {
            return;
        }

        $service = app(DiscogsService::class);

        $masterId = $result['master_id'] ?? null;
        $releaseId = $result['id'] ?? null;
        $type = $result['type'] ?? 'master';

        if ($type === 'master' && $masterId) {
            $details = $service->getMasterDetails($masterId);
        } elseif ($releaseId) {
            $details = $service->getReleaseDetails($releaseId);
        } else {
            return;
        }

        if (! $details) {
            session()->flash('error', 'Could not fetch release details.');

            return;
        }

        $this->selectedRelease = $details;
        $this->step = 'configure';
    }

    public function save(): void
    {
        if (! $this->selectedRelease) {
            return;
        }

        $discogsId = $this->selectedRelease['discogs_id'] ?? null;
        $discogsMasterId = $this->selectedRelease['discogs_master_id'] ?? null;

        if ($discogsId || $discogsMasterId) {
            $existing = Album::where('user_id', Auth::id())
                ->where(function ($q) use ($discogsId, $discogsMasterId) {
                    if ($discogsId) {
                        $q->where('discogs_id', $discogsId);
                    }
                    if ($discogsMasterId) {
                        $q->orWhere('discogs_master_id', $discogsMasterId);
                    }
                })
                ->exists();

            if ($existing) {
                session()->flash('error', 'This album is already in your collection.');

                return;
            }
        }

        $album = Album::create([
            'user_id' => Auth::id(),
            'title' => $this->selectedRelease['title'],
            'artist' => $this->selectedRelease['artist'],
            'genre' => $this->selectedRelease['genre'] ?? [],
            'styles' => $this->selectedRelease['styles'] ?? [],
            'year' => $this->selectedRelease['year'],
            'format' => $this->selectedRelease['format'],
            'label' => $this->selectedRelease['label'],
            'country' => $this->selectedRelease['country'],
            'cover_url' => $this->selectedRelease['cover_url'],
            'tracklist' => $this->selectedRelease['tracklist'] ?? [],
            'status' => $this->status,
            'ownership' => $this->ownership,
            'rating' => $this->rating,
            'discogs_id' => $this->selectedRelease['discogs_id'],
            'discogs_master_id' => $this->selectedRelease['discogs_master_id'],
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('message', "{$album->title} added to your collection.");

        $this->redirect(route('albums.show', $album));
    }

    public function back(): void
    {
        $this->step = match ($this->step) {
            'configure' => 'results',
            'results' => 'search',
            default => 'search',
        };
    }

    public function render()
    {
        return view('livewire.albums.album-discogs-search', [
            'statuses' => CollectionStatus::cases(),
            'ownershipStatuses' => OwnershipStatus::cases(),
        ])->layout('layouts.app');
    }
}
