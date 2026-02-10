<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Enums\WatchingStatus;
use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class MovieShow extends Component
{
    use AuthorizesRequests;

    public Movie $movie;

    public string $posterUrlInput = '';

    public bool $showPosterForm = false;

    public ?array $fetchedMetadata = null;

    public bool $showMetadataPreview = false;

    public function mount(Movie $movie): void
    {
        $this->authorize('view', $movie);
        $this->movie = $movie;
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->movie);

        $updates = ['status' => $status];

        if ($status === 'watched' && ! $this->movie->date_watched) {
            $updates['date_watched'] = now();
        }

        $this->movie->update($updates);
        $this->movie->refresh();
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->movie);

        $this->movie->update([
            'rating' => $rating,
            'date_rated' => now(),
        ]);
        $this->movie->refresh();
    }

    public function deleteMovie(): void
    {
        $this->authorize('delete', $this->movie);

        $this->movie->delete();

        session()->flash('message', 'Movie deleted successfully.');

        $this->redirect(route('movies.index'));
    }

    public function savePosterUrl(): void
    {
        $this->authorize('update', $this->movie);

        $url = trim($this->posterUrlInput);

        if ($url === '') {
            $this->showPosterForm = false;
            return;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->addError('posterUrlInput', 'Please enter a valid URL.');
            return;
        }

        $this->movie->update(['poster_url' => $url]);
        $this->movie->refresh();
        $this->posterUrlInput = '';
        $this->showPosterForm = false;

        session()->flash('message', 'Poster updated.');
    }

    public function togglePosterForm(): void
    {
        $this->showPosterForm = ! $this->showPosterForm;
        $this->posterUrlInput = $this->movie->poster_url ?? '';
        $this->resetErrorBag('posterUrlInput');
    }

    public function fetchMetadata(): void
    {
        $this->authorize('update', $this->movie);

        $service = app(TmdbService::class);
        if (! $service->isConfigured()) {
            session()->flash('error', 'TMDB is not configured.');
            return;
        }

        $metadata = null;
        $isEpisode = $this->movie->isLikelyEpisode();

        if ($isEpisode) {
            if ($this->movie->imdb_id) {
                $episodeDetails = $service->findEpisodeDetailsByImdbId($this->movie->imdb_id);
                if ($episodeDetails) {
                    $metadata = $episodeDetails;
                }
            }
            if (! $metadata) {
                $showName = $this->movie->show_name;
                if (empty($showName) && str_contains($this->movie->title, ':')) {
                    $showName = trim(explode(':', $this->movie->title, 2)[0]);
                }
                if ($showName) {
                    $posterUrl = $service->searchTVShowPosterByTitle($showName);
                    if ($posterUrl) {
                        $metadata = ['poster_url' => $posterUrl];
                    }
                }
            }
        } else {
            if ($this->movie->imdb_id) {
                $metadata = $service->findByImdbId($this->movie->imdb_id);
            }
            if (! $metadata && $this->movie->title) {
                $metadata = $service->searchByTitle($this->movie->title, $this->movie->year);
            }
        }

        if (! $metadata) {
            session()->flash('error', 'No metadata found on TMDB for this entry.');
            return;
        }

        $this->fetchedMetadata = $metadata;
        $this->showMetadataPreview = true;
    }

    public function applyMetadata(): void
    {
        $this->authorize('update', $this->movie);

        if (! $this->fetchedMetadata) {
            return;
        }

        $fields = ['description', 'poster_url', 'runtime_minutes', 'release_date', 'genres', 'director', 'show_name', 'season_number', 'episode_number'];
        $updates = [];

        foreach ($fields as $field) {
            if (! empty($this->fetchedMetadata[$field]) && empty($this->movie->$field)) {
                $updates[$field] = $this->fetchedMetadata[$field];
            }
        }

        // Always fill title/year from TMDB if we have them and current is empty
        if (! empty($this->fetchedMetadata['title']) && empty($this->movie->title)) {
            $updates['title'] = $this->fetchedMetadata['title'];
        }
        if (! empty($this->fetchedMetadata['year']) && empty($this->movie->year)) {
            $updates['year'] = $this->fetchedMetadata['year'];
        }

        if (! empty($updates)) {
            $updates['metadata_fetched_at'] = now();
            $this->movie->update($updates);

            // Propagate poster to siblings
            $posterUrl = $updates['poster_url'] ?? $this->movie->poster_url;
            $showName = $updates['show_name'] ?? $this->movie->show_name;
            if ($posterUrl && ($this->movie->isLikelyEpisode() || in_array($this->movie->title_type, ['TV Series', 'TV Mini Series']))) {
                $titlePrefix = str_contains($this->movie->title, ':')
                    ? trim(explode(':', $this->movie->title, 2)[0])
                    : ($this->movie->title_type !== 'TV Episode' ? $this->movie->title : null);
                Movie::propagateShowPoster(
                    $this->movie->user_id,
                    $showName,
                    $titlePrefix,
                    $posterUrl,
                    $showName,
                );
            }
        } else {
            $this->movie->update(['metadata_fetched_at' => now()]);
        }

        $this->movie->refresh();
        $this->fetchedMetadata = null;
        $this->showMetadataPreview = false;

        $count = count($updates) - (isset($updates['metadata_fetched_at']) ? 1 : 0);
        session()->flash('message', $count > 0 ? "Updated {$count} field(s) from TMDB." : 'No new data to fill — all fields already populated.');
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

    public function getSiblingEpisodes()
    {
        if (! $this->movie->isLikelyEpisode()) {
            return collect();
        }

        $query = Movie::where('user_id', $this->movie->user_id)
            ->where('id', '!=', $this->movie->id);

        // Match by show_name if set, otherwise by title prefix before ":"
        if ($this->movie->show_name) {
            $query->where('show_name', $this->movie->show_name);
        } else {
            $prefix = \Illuminate\Support\Str::before($this->movie->title, ':');
            if ($prefix !== $this->movie->title) {
                $query->where('title', 'like', $prefix . ':%');
            } else {
                return collect();
            }
        }

        // If current movie has a season, filter to same season
        if ($this->movie->season_number !== null) {
            $query->where('season_number', $this->movie->season_number);
        }

        return $query
            ->orderByRaw('season_number IS NULL, season_number ASC')
            ->orderByRaw('episode_number IS NULL, episode_number ASC')
            ->orderBy('title', 'asc')
            ->get();
    }

    public function getShowName(): string
    {
        return $this->movie->show_name
            ?? \Illuminate\Support\Str::before($this->movie->title, ':');
    }

    public function getParentShow(): ?Movie
    {
        if (! $this->movie->isLikelyEpisode()) {
            return null;
        }

        $showName = $this->getShowName();

        // Find a parent entry that is a TV Series/Mini Series matching this show
        return Movie::where('user_id', $this->movie->user_id)
            ->where('id', '!=', $this->movie->id)
            ->whereIn('title_type', ['TV Series', 'TV Mini Series'])
            ->where(function ($q) use ($showName) {
                $q->where('title', $showName)
                    ->orWhere('show_name', $showName);
            })
            ->first();
    }

    public function render()
    {
        $siblingEpisodes = $this->getSiblingEpisodes();
        $showName = $siblingEpisodes->isNotEmpty() ? $this->getShowName() : null;

        // Merge current episode into siblings and sort all together
        if ($siblingEpisodes->isNotEmpty()) {
            $allEpisodes = $siblingEpisodes->push($this->movie)
                ->sortBy([
                    ['season_number', 'asc'],
                    ['episode_number', 'asc'],
                    ['title', 'asc'],
                ])
                ->values();
        } else {
            $allEpisodes = collect();
        }

        return view('livewire.movies.movie-show', [
            'statuses' => $this->getStatuses(),
            'allEpisodes' => $allEpisodes,
            'showName' => $showName,
            'parentShow' => $this->getParentShow(),
        ])->layout('layouts.app');
    }
}
