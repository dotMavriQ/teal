<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Jobs\FetchMovieMetadata;
use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class MovieMetadataEnrichment extends Component
{
    public array $sourcePriority = ['current', 'tmdb'];

    public array $moviesNeedingEnrichment = [];

    public bool $hasScanned = false;

    public bool $isScanning = false;

    public ?array $jobStatus = null;

    public bool $showReviewModal = false;

    public ?int $reviewingMovieId = null;

    public ?array $reviewingMovie = null;

    public ?array $reviewingMetadata = null;

    public array $selectedFields = [];

    public array $fetchedData = [];

    public int $batchLimit = 100;

    protected const ENRICHABLE_FIELDS = ['description', 'poster_url', 'runtime_minutes', 'release_date', 'genres', 'director'];

    public function mount(): void
    {
        $this->refreshJobStatus();
    }

    public function refreshJobStatus(): void
    {
        $this->jobStatus = FetchMovieMetadata::getStatus(Auth::id());
    }

    public function clearJobStatus(): void
    {
        FetchMovieMetadata::clearStatus(Auth::id());
        $this->jobStatus = null;
    }

    public function moveSourceUp(string $source): void
    {
        $index = array_search($source, $this->sourcePriority);
        if ($index > 0) {
            $temp = $this->sourcePriority[$index - 1];
            $this->sourcePriority[$index - 1] = $source;
            $this->sourcePriority[$index] = $temp;
        }
    }

    public function moveSourceDown(string $source): void
    {
        $index = array_search($source, $this->sourcePriority);
        if ($index < count($this->sourcePriority) - 1) {
            $temp = $this->sourcePriority[$index + 1];
            $this->sourcePriority[$index + 1] = $source;
            $this->sourcePriority[$index] = $temp;
        }
    }

    public function scanLibrary(): void
    {
        $this->isScanning = true;
        $this->hasScanned = false;
        $this->fetchedData = [];

        $this->moviesNeedingEnrichment = Movie::query()
            ->where('user_id', Auth::id())
            ->orderByRaw('metadata_fetched_at IS NOT NULL, metadata_fetched_at ASC')
            ->get(['id', 'title', 'director', 'imdb_id', 'year', 'description', 'poster_url', 'runtime_minutes', 'release_date', 'genres', 'metadata_fetched_at'])
            ->map(function ($movie) {
                $missing = $this->getMissingFields($movie);

                return [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'director' => $movie->director,
                    'imdb_id' => $movie->imdb_id,
                    'year' => $movie->year,
                    'is_episode' => stripos($movie->title, 'episode') !== false,
                    'metadata_fetched_at' => $movie->metadata_fetched_at?->toIso8601String(),
                    'current' => [
                        'description' => $movie->description,
                        'poster_url' => $movie->poster_url,
                        'runtime_minutes' => $movie->runtime_minutes,
                        'release_date' => $movie->release_date?->format('Y-m-d'),
                        'genres' => $movie->genres,
                        'director' => $movie->director,
                    ],
                    'missing' => $missing,
                    'has_missing' => ! empty($missing),
                ];
            })
            ->toArray();

        $this->hasScanned = true;
        $this->isScanning = false;
    }

    protected function getMissingFields(Movie $movie): array
    {
        $missing = [];

        if (empty($movie->description)) {
            $missing[] = 'description';
        }
        if (empty($movie->poster_url)) {
            $missing[] = 'poster_url';
        }
        if (empty($movie->runtime_minutes)) {
            $missing[] = 'runtime_minutes';
        }
        if (empty($movie->release_date)) {
            $missing[] = 'release_date';
        }
        if (empty($movie->genres)) {
            $missing[] = 'genres';
        }
        if (empty($movie->director)) {
            $missing[] = 'director';
        }

        return $missing;
    }

    public function startBackgroundFetch(): void
    {
        if (FetchMovieMetadata::isRunning(Auth::id())) {
            session()->flash('error', 'A metadata fetch is already running.');

            return;
        }

        $moviesToFetch = collect($this->moviesNeedingEnrichment)
            ->filter(fn ($movie) => $movie['has_missing'])
            ->filter(fn ($movie) => ! $movie['is_episode'])
            ->take($this->batchLimit)
            ->pluck('id')
            ->toArray();

        if (empty($moviesToFetch)) {
            session()->flash('message', 'No movies need metadata fetching.');

            return;
        }

        $initialStatus = [
            'status' => 'running',
            'progress' => 0,
            'total' => count($moviesToFetch),
            'fetched' => 0,
            'applied' => 0,
            'started_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];
        Cache::put('movie_metadata_fetch_' . Auth::id(), $initialStatus, now()->addHours(2));

        $this->jobStatus = $initialStatus;

        $job = new FetchMovieMetadata(
            Auth::id(),
            $moviesToFetch,
            $this->sourcePriority
        );
        $job->handle();

        $this->refreshJobStatus();

        $fetched = $this->jobStatus['fetched'] ?? 0;
        $applied = $this->jobStatus['applied'] ?? 0;
        session()->flash('message', "Metadata fetch completed! Updated {$applied} of {$fetched} movies.");
    }

    public function fetchSingleMovie(int $id): void
    {
        $movieData = collect($this->moviesNeedingEnrichment)->firstWhere('id', $id);

        if (! $movieData) {
            return;
        }

        $service = app(TmdbService::class);
        $metadata = null;

        if (! empty($movieData['imdb_id'])) {
            $metadata = $service->findByImdbId($movieData['imdb_id']);
        }

        if (! $metadata && ! empty($movieData['title'])) {
            $metadata = $service->searchByTitle($movieData['title'], $movieData['year']);
        }

        if ($metadata) {
            $this->fetchedData[$id] = $metadata;
            $this->reviewingMetadata = $metadata;
            $this->selectedFields = $this->getFieldsToApply($movieData, $metadata);
        }
    }

    public function startReview(int $id): void
    {
        $movieData = collect($this->moviesNeedingEnrichment)->firstWhere('id', $id);

        if (! $movieData) {
            return;
        }

        $this->reviewingMovieId = $id;
        $this->reviewingMovie = $movieData;
        $this->reviewingMetadata = $this->fetchedData[$id] ?? null;

        $this->selectedFields = $this->getFieldsToApply($movieData, $this->reviewingMetadata);

        $this->showReviewModal = true;
    }

    protected function getFieldsToApply(array $movieData, ?array $metadata): array
    {
        if (! $metadata) {
            return [];
        }

        $fields = [];
        $currentFirst = $this->sourcePriority[0] === 'current';

        foreach (self::ENRICHABLE_FIELDS as $field) {
            $hasCurrentValue = ! empty($movieData['current'][$field]);
            $hasNewValue = ! empty($metadata[$field]);

            if (! $hasNewValue) {
                continue;
            }

            if (! $hasCurrentValue) {
                $fields[] = $field;
            } elseif (! $currentFirst) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    public function applyMetadata(): void
    {
        if (! $this->reviewingMovieId || ! $this->reviewingMetadata || empty($this->selectedFields)) {
            $this->closeReviewModal();

            return;
        }

        $movie = Movie::query()
            ->where('user_id', Auth::id())
            ->find($this->reviewingMovieId);

        if (! $movie) {
            $this->closeReviewModal();

            return;
        }

        $updateData = [];

        foreach ($this->selectedFields as $field) {
            if (isset($this->reviewingMetadata[$field]) && $this->reviewingMetadata[$field] !== null) {
                $updateData[$field] = $this->reviewingMetadata[$field];
            }
        }

        if (! empty($updateData)) {
            $movie->update($updateData);
        }

        $this->updateLocalMovieData($this->reviewingMovieId, $updateData);

        $this->closeReviewModal();

        session()->flash('message', 'Metadata applied successfully.');
    }

    public function skipMovie(): void
    {
        $this->closeReviewModal();
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->reviewingMovieId = null;
        $this->reviewingMovie = null;
        $this->reviewingMetadata = null;
        $this->selectedFields = [];
    }

    protected function updateLocalMovieData(int $movieId, array $updateData): void
    {
        foreach ($this->moviesNeedingEnrichment as $index => $movieData) {
            if ($movieData['id'] === $movieId) {
                foreach ($updateData as $field => $value) {
                    $this->moviesNeedingEnrichment[$index]['current'][$field] = $value;

                    $missingIndex = array_search($field, $this->moviesNeedingEnrichment[$index]['missing']);
                    if ($missingIndex !== false) {
                        unset($this->moviesNeedingEnrichment[$index]['missing'][$missingIndex]);
                        $this->moviesNeedingEnrichment[$index]['missing'] = array_values($this->moviesNeedingEnrichment[$index]['missing']);
                    }
                }

                $this->moviesNeedingEnrichment[$index]['has_missing'] = ! empty($this->moviesNeedingEnrichment[$index]['missing']);
                break;
            }
        }
    }

    public function getSourceLabel(string $source): string
    {
        return match ($source) {
            'current' => 'Keep Current Values',
            'tmdb' => 'TMDB (The Movie Database)',
            default => $source,
        };
    }

    public function getMoviesWithMissingCount(): int
    {
        return collect($this->moviesNeedingEnrichment)->where('has_missing', true)->count();
    }

    public function getFetchedCount(): int
    {
        return count($this->fetchedData);
    }

    public function isJobRunning(): bool
    {
        return $this->jobStatus && $this->jobStatus['status'] === 'running';
    }

    public function isJobCompleted(): bool
    {
        return $this->jobStatus && $this->jobStatus['status'] === 'completed';
    }

    public function render()
    {
        if ($this->jobStatus && $this->jobStatus['status'] === 'running') {
            $this->refreshJobStatus();
        }

        return view('livewire.movies.movie-metadata-enrichment')
            ->layout('layouts.app');
    }
}
