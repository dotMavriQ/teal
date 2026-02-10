<?php

declare(strict_types=1);

namespace App\Livewire\Anime;

use App\Enums\WatchingStatus;
use App\Models\Anime;
use App\Services\JikanService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class AnimeShow extends Component
{
    use AuthorizesRequests;

    public Anime $anime;

    public string $posterUrlInput = '';

    public bool $showPosterForm = false;

    public ?array $fetchedMetadata = null;

    public bool $showMetadataPreview = false;

    public function mount(Anime $anime): void
    {
        $this->authorize('view', $anime);
        $this->anime = $anime;
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->anime);

        $updates = ['status' => $status];

        if ($status === 'watched' && ! $this->anime->date_finished) {
            $updates['date_finished'] = now();
        }

        $this->anime->update($updates);
        $this->anime->refresh();
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->anime);

        $this->anime->update(['rating' => $rating]);
        $this->anime->refresh();
    }

    public function deleteAnime(): void
    {
        $this->authorize('delete', $this->anime);

        $this->anime->delete();

        session()->flash('message', 'Anime deleted successfully.');

        $this->redirect(route('anime.index'));
    }

    public function savePosterUrl(): void
    {
        $this->authorize('update', $this->anime);

        $url = trim($this->posterUrlInput);

        if ($url === '') {
            $this->showPosterForm = false;

            return;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->addError('posterUrlInput', 'Please enter a valid URL.');

            return;
        }

        $this->anime->update(['poster_url' => $url]);
        $this->anime->refresh();
        $this->posterUrlInput = '';
        $this->showPosterForm = false;

        session()->flash('message', 'Poster updated.');
    }

    public function togglePosterForm(): void
    {
        $this->showPosterForm = ! $this->showPosterForm;
        $this->posterUrlInput = $this->anime->poster_url ?? '';
        $this->resetErrorBag('posterUrlInput');
    }

    public function fetchMetadata(): void
    {
        $this->authorize('update', $this->anime);

        $service = app(JikanService::class);
        $metadata = null;

        if ($this->anime->mal_id) {
            $metadata = $service->findByMalId($this->anime->mal_id);
        }

        if (! $metadata && $this->anime->title) {
            $metadata = $service->searchByTitle($this->anime->title);
        }

        if (! $metadata) {
            session()->flash('error', 'No metadata found on Jikan/MAL for this entry.');

            return;
        }

        $this->fetchedMetadata = $metadata;
        $this->showMetadataPreview = true;
    }

    public function applyMetadata(): void
    {
        $this->authorize('update', $this->anime);

        if (! $this->fetchedMetadata) {
            return;
        }

        $fields = ['description', 'poster_url', 'runtime_minutes', 'genres', 'studios', 'episodes_total', 'media_type', 'original_title', 'mal_score', 'mal_url'];
        $updates = [];

        foreach ($fields as $field) {
            if (! empty($this->fetchedMetadata[$field]) && empty($this->anime->$field)) {
                $updates[$field] = $this->fetchedMetadata[$field];
            }
        }

        if (! empty($this->fetchedMetadata['year']) && empty($this->anime->year)) {
            $updates['year'] = $this->fetchedMetadata['year'];
        }

        if (! empty($this->fetchedMetadata['mal_id']) && empty($this->anime->mal_id)) {
            $updates['mal_id'] = $this->fetchedMetadata['mal_id'];
        }

        if (! empty($updates)) {
            $updates['metadata_fetched_at'] = now();
            $this->anime->update($updates);
        } else {
            $this->anime->update(['metadata_fetched_at' => now()]);
        }

        $this->anime->refresh();
        $this->fetchedMetadata = null;
        $this->showMetadataPreview = false;

        $count = count($updates) - (isset($updates['metadata_fetched_at']) ? 1 : 0);
        session()->flash('message', $count > 0 ? "Updated {$count} field(s) from Jikan." : 'No new data to fill — all fields already populated.');
    }

    public function dismissMetadata(): void
    {
        $this->fetchedMetadata = null;
        $this->showMetadataPreview = false;
    }

    public function getStatuses(): array
    {
        return WatchingStatus::cases();
    }

    public function render()
    {
        return view('livewire.anime.anime-show', [
            'statuses' => $this->getStatuses(),
        ])->layout('layouts.app');
    }
}
