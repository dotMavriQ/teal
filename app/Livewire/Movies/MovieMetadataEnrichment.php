<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Jobs\FetchMovieMetadata;
use App\Models\Movie;
use App\Services\TmdbService;
use App\Services\TraktService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MovieMetadataEnrichment extends Component
{
    use \App\Livewire\Concerns\WithMetadataEnrichment;
    use \App\Livewire\Concerns\WithSourcePriority;

    /** @var list<string> */
    public array $sourcePriority = ['current', 'trakt', 'tmdb'];

    /** @var array<int, array<string, mixed>> */
    public array $moviesNeedingEnrichment = [];

    public bool $hasScanned = false;

    public bool $isScanning = false;

    /** @var array<array-key, mixed>|null */
    public ?array $jobStatus = null;

    public bool $showReviewModal = false;

    public ?int $reviewingMovieId = null;

    /** @var array<string, mixed>|null */
    public ?array $reviewingMovie = null;

    /** @var array<string, mixed>|null */
    public ?array $reviewingMetadata = null;

    /** @var list<string> */
    public array $selectedFields = [];

    /** @var array<int, array<string, mixed>> */
    public array $fetchedData = [];

    public int $batchLimit = 100;

    public string $activeTab = 'all';

    /** @var array<int, array<string, mixed>> */
    public array $orphanEpisodes = [];

    /** @var list<string> */
    protected const ENRICHABLE_FIELDS = ['description', 'poster_url', 'runtime_minutes', 'release_date', 'genres', 'director', 'show_name', 'season_number', 'episode_number'];

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function enrichmentList(): array
    {
        return $this->moviesNeedingEnrichment;
    }

    /**
     * @param  array<int, array<string, mixed>>  $list
     */
    protected function setEnrichmentList(array $list): void
    {
        $this->moviesNeedingEnrichment = $list;
    }

    protected function setReviewingId(?int $id): void
    {
        $this->reviewingMovieId = $id;
    }

    /**
     * @param  array<string, mixed>|null  $item
     */
    protected function setReviewingItem(?array $item): void
    {
        $this->reviewingMovie = $item;
    }

    /**
     * @return list<string>
     */
    protected function enrichableFields(): array
    {
        return self::ENRICHABLE_FIELDS;
    }

    /**
     * @param  array<array-key, mixed>|null  $data
     */
    private function intFrom(?array $data, string $key): int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function strFrom(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    public function mount(): void
    {
        $this->refreshJobStatus();
    }

    public function refreshJobStatus(): void
    {
        $this->jobStatus = FetchMovieMetadata::getStatus((int) Auth::id());
    }

    public function clearJobStatus(): void
    {
        FetchMovieMetadata::clearStatus((int) Auth::id());
        $this->jobStatus = null;
    }

    public function scanLibrary(): void
    {
        $this->isScanning = true;
        $this->hasScanned = false;
        $this->fetchedData = [];

        $randomFunction = in_array(DB::getDriverName(), ['sqlite', 'pgsql']) ? 'RANDOM()' : 'RAND()';
        $this->moviesNeedingEnrichment = Movie::query()
            ->where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '')
                    ->orWhereNull('poster_url')
                    ->orWhere('poster_url', '')
                    ->orWhereNull('runtime_minutes')
                    ->orWhere('runtime_minutes', 0)
                    ->orWhereNull('release_date')
                    ->orWhereNull('genres')
                    ->orWhere('genres', '')
                    ->orWhereNull('director')
                    ->orWhere('director', '');
            })
            ->orderByRaw("metadata_fetched_at IS NULL DESC, {$randomFunction}")
            ->get(['id', 'title', 'title_type', 'director', 'imdb_id', 'year', 'description', 'poster_url', 'runtime_minutes', 'release_date', 'genres', 'season_number', 'episode_number', 'show_name', 'metadata_fetched_at'])
            ->map(function ($movie) {
                $missing = $this->getMissingFields($movie);

                return [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'title_type' => $movie->title_type,
                    'director' => $movie->director,
                    'imdb_id' => $movie->imdb_id,
                    'year' => $movie->year,
                    'is_episode' => $movie->isEpisode() || $movie->title_type === 'TV Episode',
                    'show_name' => $movie->show_name,
                    'metadata_fetched_at' => $movie->metadata_fetched_at?->toIso8601String(),
                    'current' => [
                        'description' => $movie->description,
                        'poster_url' => $movie->poster_url,
                        'runtime_minutes' => $movie->runtime_minutes,
                        'release_date' => $movie->release_date?->format('Y-m-d'),
                        'genres' => $movie->genres,
                        'director' => $movie->director,
                        'show_name' => $movie->show_name,
                        'season_number' => $movie->season_number,
                        'episode_number' => $movie->episode_number,
                    ],
                    'missing' => $missing,
                    'has_missing' => ! empty($missing),
                ];
            })
            ->filter(fn ($movie) => (bool) $movie['has_missing'])
            ->sortByDesc(fn ($movie) => count($movie['missing']))
            ->values()
            ->all();

        $this->scanOrphanEpisodes();

        $this->hasScanned = true;
        $this->isScanning = false;
    }

    /**
     * @return list<string>
     */
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
        if (FetchMovieMetadata::isRunning((int) Auth::id())) {
            session()->flash('error', 'A metadata fetch is already running.');

            return;
        }

        $moviesToFetch = collect($this->moviesNeedingEnrichment)
            ->filter(fn ($movie) => (bool) $movie['has_missing'])
            ->take($this->batchLimit)
            ->pluck('id')
            ->all();

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
        Cache::put('movie_metadata_fetch_'.Auth::id(), $initialStatus, now()->addHours(2));

        $this->jobStatus = $initialStatus;

        $job = new FetchMovieMetadata(
            (int) Auth::id(),
            array_values(array_map(fn ($id): int => is_numeric($id) ? (int) $id : 0, $moviesToFetch)),
            $this->sourcePriority
        );
        $job->handle();

        $this->refreshJobStatus();

        $fetched = $this->intFrom($this->jobStatus, 'fetched');
        $applied = $this->intFrom($this->jobStatus, 'applied');
        session()->flash('message', "Metadata fetch completed! Updated {$applied} of {$fetched} movies.");
    }

    public function fetchSingleMovie(int $id): void
    {
        $movieData = collect($this->moviesNeedingEnrichment)->firstWhere('id', $id);

        if (! is_array($movieData)) {
            return;
        }

        $tmdb = app(TmdbService::class);
        $trakt = app(TraktService::class);
        $metadata = null;

        $imdbId = $this->strFrom($movieData, 'imdb_id');
        $title = $this->strFrom($movieData, 'title');
        $isEpisode = ! empty($movieData['is_episode']) || ($movieData['title_type'] ?? '') === 'TV Episode';

        if ($isEpisode) {
            // Episode: fetch show poster + episode details from TMDB
            if ($imdbId !== '') {
                $episodeDetails = $tmdb->findEpisodeDetailsByImdbId($imdbId);
                if ($episodeDetails) {
                    $metadata = $episodeDetails;
                }
            }

            // Fallback: search TV shows by show_name or title prefix
            if (! $metadata) {
                $showName = $this->strFrom($movieData, 'show_name');
                if ($showName === '' && str_contains($title, ':')) {
                    $showName = trim(explode(':', $title, 2)[0]);
                }
                if ($showName !== '') {
                    $posterUrl = $tmdb->searchTVShowPosterByTitle($showName);
                    if ($posterUrl) {
                        $metadata = ['poster_url' => $posterUrl];
                    }
                }
            }
        } else {
            // Fetch from each source in priority order and merge results
            $sources = $this->getOrderedSources($tmdb, $trakt);
            $metadata = $this->fetchFromSources($sources, $movieData);
        }

        if ($metadata) {
            $this->fetchedData[$id] = $metadata;
            $this->reviewingMetadata = $metadata;
            $this->selectedFields = $this->getFieldsToApply($movieData, $metadata);
        }
    }

    /**
     * Return source services in priority order (excluding 'current').
     *
     * @return array<string, TmdbService|TraktService>
     */
    protected function getOrderedSources(TmdbService $tmdb, TraktService $trakt): array
    {
        $sourceMap = [
            'trakt' => $trakt,
            'tmdb' => $tmdb,
        ];

        $ordered = [];
        foreach ($this->sourcePriority as $source) {
            if (isset($sourceMap[$source])) {
                $ordered[$source] = $sourceMap[$source];
            }
        }

        return $ordered;
    }

    /**
     * Fetch metadata from sources in priority order, merging to fill gaps.
     *
     * @param  array<string, TmdbService|TraktService>  $sources
     * @param  array<string, mixed>  $movieData
     * @return array<string, mixed>|null
     */
    protected function fetchFromSources(array $sources, array $movieData): ?array
    {
        $merged = null;
        $imdbId = $this->strFrom($movieData, 'imdb_id');
        $title = $this->strFrom($movieData, 'title');
        $year = is_numeric($movieData['year'] ?? null) ? (int) $movieData['year'] : null;

        foreach ($sources as $service) {
            $result = null;

            if ($imdbId !== '') {
                $result = $service->findByImdbId($imdbId);
            }

            // Only fall back to title search if no IMDb ID exists
            if (! $result && $imdbId === '' && $title !== '') {
                $result = $service->searchByTitle($title, $year);
            }

            if (! $result) {
                continue;
            }

            if ($merged === null) {
                $merged = $result;
            } else {
                // Fill empty fields from this source
                foreach ($result as $key => $value) {
                    if (! empty($value) && empty($merged[$key])) {
                        $merged[$key] = $value;
                    }
                }
            }
        }

        return $merged;
    }

    public function startReview(int $id): void
    {
        $this->openReviewFor($id);
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

        $updateData = $this->buildUpdateData();

        if (! empty($updateData)) {
            $movie->update($updateData);
        }

        // Propagate show poster to siblings when applying to an episode or show
        $posterRaw = $updateData['poster_url'] ?? $movie->poster_url;
        $posterToPropagate = is_string($posterRaw) ? $posterRaw : null;
        $showNameRaw = $updateData['show_name'] ?? $movie->show_name;
        $showNameToPropagate = is_string($showNameRaw) ? $showNameRaw : null;
        $isEpisodeOrShow = $movie->isLikelyEpisode() || in_array($movie->title_type, ['TV Series', 'TV Mini Series'], true);

        $propagated = 0;
        if ($isEpisodeOrShow && $posterToPropagate !== null) {
            $titlePrefix = str_contains($movie->title, ':')
                ? trim(explode(':', $movie->title, 2)[0])
                : ($movie->title_type !== 'TV Episode' ? $movie->title : null);

            $propagated = Movie::propagateShowPoster(
                (int) Auth::id(),
                $showNameToPropagate,
                $titlePrefix,
                $posterToPropagate,
                $showNameToPropagate,
            );
        }

        $this->updateLocalItemData($this->reviewingMovieId, $updateData);

        $this->closeReviewModal();

        $msg = 'Metadata applied successfully.';
        if (! empty($propagated)) {
            $msg .= " Poster propagated to {$propagated} sibling(s).";
        }
        session()->flash('message', $msg);
    }

    public function skipMovie(): void
    {
        $this->closeReviewModal();
    }

    public function getSourceLabel(string $source): string
    {
        return match ($source) {
            'current' => 'Keep Current Values',
            'trakt' => 'Trakt',
            'tmdb' => 'TMDB (The Movie Database)',
            default => $source,
        };
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFilteredMovies(): array
    {
        if ($this->activeTab === 'tv') {
            return collect($this->moviesNeedingEnrichment)
                ->filter(fn ($m) => in_array($m['title_type'] ?? '', ['TV Series', 'TV Mini Series', 'TV Episode'], true))
                ->values()
                ->all();
        }

        if ($this->activeTab === 'orphans') {
            return [];
        }

        return $this->moviesNeedingEnrichment;
    }

    public function getTvCount(): int
    {
        return collect($this->moviesNeedingEnrichment)
            ->filter(fn ($m) => in_array($m['title_type'] ?? '', ['TV Series', 'TV Mini Series', 'TV Episode'], true))
            ->count();
    }

    public function scanOrphanEpisodes(): void
    {
        $userId = Auth::id();

        // Get all TV Series / TV Mini Series titles for this user
        $seriesTitles = Movie::query()
            ->where('user_id', $userId)
            ->whereIn('title_type', ['TV Series', 'TV Mini Series'])
            ->pluck('title')
            ->toArray();

        // Find TV Episodes with no show_name, or whose show_name doesn't match any series title
        $orphans = Movie::query()
            ->where('user_id', $userId)
            ->where('title_type', 'TV Episode')
            ->where(function ($query) use ($seriesTitles) {
                $query->whereNull('show_name')
                    ->orWhere('show_name', '');
                if (! empty($seriesTitles)) {
                    $query->orWhereNotIn('show_name', $seriesTitles);
                }
            })
            ->orderBy('title')
            ->get(['id', 'title', 'title_type', 'imdb_id', 'year', 'show_name', 'season_number', 'episode_number', 'poster_url'])
            ->map(fn ($movie) => [
                'id' => $movie->id,
                'title' => $movie->title,
                'imdb_id' => $movie->imdb_id,
                'year' => $movie->year,
                'show_name' => $movie->show_name,
                'season_episode' => $movie->season_episode_label,
                'has_show_name' => ! empty($movie->show_name),
            ])
            ->all();

        $this->orphanEpisodes = $orphans;
    }

    public function linkOrphanToShow(int $movieId, string $showName): void
    {
        $movie = Movie::query()
            ->where('user_id', Auth::id())
            ->where('title_type', 'TV Episode')
            ->find($movieId);

        if (! $movie) {
            return;
        }

        $movie->update(['show_name' => $showName]);

        // Propagate to siblings with same title prefix
        $titlePrefix = str_contains($movie->title, ':')
            ? trim(explode(':', $movie->title, 2)[0])
            : null;

        $propagated = 0;
        if ($titlePrefix) {
            $propagated = Movie::query()
                ->where('user_id', Auth::id())
                ->where('title_type', 'TV Episode')
                ->where(function ($q) use ($titlePrefix) {
                    $q->where('title', 'like', $titlePrefix.':%');
                })
                ->where(function ($q) {
                    $q->whereNull('show_name')->orWhere('show_name', '');
                })
                ->update(['show_name' => $showName]);
        }

        // Re-scan orphans to reflect changes
        $this->scanOrphanEpisodes();

        $msg = "Linked episode to \"{$showName}\".";
        if ($propagated > 0) {
            $msg .= " Also linked {$propagated} sibling episode(s).";
        }
        session()->flash('message', $msg);
    }

    #[Layout('layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        if ($this->jobStatus && $this->jobStatus['status'] === 'running') {
            $this->refreshJobStatus();
        }

        return view('livewire.movies.movie-metadata-enrichment');
    }
}
