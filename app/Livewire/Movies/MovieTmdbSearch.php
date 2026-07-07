<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Enums\WatchingStatus;
use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class MovieTmdbSearch extends Component
{
    // URL-addressable wizard state. The current step is DERIVED (see step()),
    // not stored, so it always matches the URL after a back/forward navigation.
    // Only `q` and `id` drive browser history — each user transition changes
    // exactly one of them, so a single Back reliably moves one level. `type`
    // and `page` ride along in the URL (for deep links / refresh) but are not
    // history entries. Nothing here is persisted; results and details are
    // re-derived from the (7-day cached) API.

    // Search state
    #[Url(as: 'q', history: true)]
    public string $query = '';

    /** @var array<array-key, mixed> */
    public array $searchResults = [];

    public int $totalPages = 0;

    #[Url(as: 'page')]
    public int $currentPage = 1;

    // Selected result details
    #[Url(as: 'type')]
    public ?string $selectedMediaType = null;

    #[Url(as: 'id', history: true)]
    public ?int $selectedTmdbId = null;

    // Movie configuration
    public string $title = '';

    public string $original_title = '';

    public string $director = '';

    public ?int $year = null;

    public ?int $runtime_minutes = null;

    public string $genres = '';

    public string $description = '';

    public string $poster_url = '';

    public string $imdb_id = '';

    public string $status = 'watchlist';

    public ?int $rating = null;

    // TV show state
    /** @var array<string, mixed> */
    public array $showData = [];

    /** @var array<array-key, mixed> */
    public array $seasons = [];

    /** @var array<int, list<array<string, mixed>>> season_number => episodes[] */
    public array $loadedEpisodes = [];

    /** @var array<string, bool> "S{n}E{n}" => true */
    public array $selectedEpisodes = [];

    /** @var array<string, bool> "S{n}E{n}" => true */
    public array $watchedEpisodes = [];

    // Duplicate detection
    /** @var array<array-key, mixed> */
    public array $existingImdbIds = [];

    /** @var array<array-key, mixed> */
    public array $existingEpisodeKeys = [];

    public function mount(): void
    {
        $userId = Auth::id();

        $this->existingImdbIds = Movie::where('user_id', $userId)
            ->whereNotNull('imdb_id')
            ->pluck('imdb_id')
            ->all();

        $this->existingEpisodeKeys = Movie::where('user_id', $userId)
            ->whereNotNull('show_name')
            ->whereNotNull('season_number')
            ->whereNotNull('episode_number')
            ->get(['show_name', 'season_number', 'episode_number'])
            ->map(fn ($m): string => $m->show_name.'|'.$m->season_number.'|'.$m->episode_number)
            ->all();

        $this->rehydrateFromUrl();
    }

    /**
     * On a fresh page load / deep link / refresh, rebuild the transient results
     * and selected-detail state from the URL-carried query + selection. Within a
     * session Livewire already retains these properties, so this only runs on
     * initial mount. Fetches hit the 7-day Saloon cache, so there is no cost of
     * "storing" results — nothing is persisted.
     */
    protected function rehydrateFromUrl(): void
    {
        if (trim($this->query) === '') {
            $this->selectedTmdbId = null;
            $this->selectedMediaType = null;

            return;
        }

        $result = app(TmdbService::class)->searchMulti(trim($this->query), max(1, $this->currentPage));
        $this->searchResults = is_array($result['results'] ?? null) ? $result['results'] : [];
        $this->totalPages = is_int($result['total_pages'] ?? null) ? $result['total_pages'] : 0;

        if ($this->selectedTmdbId === null) {
            return;
        }

        // Deep links may omit `type`; recover it from the result set so we know
        // which detail endpoint to call.
        if ($this->selectedMediaType === null) {
            $this->selectedMediaType = $this->mediaTypeFromResults($this->selectedTmdbId) ?? 'movie';
        }

        $this->loadDetails();
    }

    /**
     * The current wizard step, DERIVED from the URL-carried state so it is
     * always correct after a browser back/forward. Passed to the view as $step.
     */
    protected function step(): string
    {
        if (trim($this->query) === '') {
            return 'search';
        }

        if ($this->selectedTmdbId === null) {
            return 'results';
        }

        return $this->selectedMediaType === 'movie' ? 'configure_movie' : 'configure_tv';
    }

    protected function mediaTypeFromResults(int $tmdbId): ?string
    {
        foreach ($this->searchResults as $result) {
            if (is_array($result) && ($result['tmdb_id'] ?? null) === $tmdbId) {
                $mt = $result['media_type'] ?? null;

                return is_string($mt) ? $mt : null;
            }
        }

        return null;
    }

    public function search(): void
    {
        $query = trim($this->query);
        if ($query === '') {
            return;
        }

        $tmdb = app(TmdbService::class);
        $result = $tmdb->searchMulti($query, 1);

        $this->searchResults = is_array($result['results'] ?? null) ? $result['results'] : [];
        $this->totalPages = is_int($result['total_pages'] ?? null) ? $result['total_pages'] : 0;
        $this->currentPage = 1;
        // A fresh search clears any prior selection; step() then derives 'results'.
        $this->selectedTmdbId = null;
        $this->selectedMediaType = null;
    }

    public function loadPage(int $page): void
    {
        $tmdb = app(TmdbService::class);
        $result = $tmdb->searchMulti(trim($this->query), $page);

        $this->searchResults = is_array($result['results'] ?? null) ? $result['results'] : [];
        $this->currentPage = $page;
    }

    public function selectResult(int $tmdbId, string $mediaType): void
    {
        $this->selectedTmdbId = $tmdbId;
        $this->selectedMediaType = $mediaType;

        $this->loadDetails();
    }

    /**
     * Fetch and populate the detail/configure fields for the currently selected
     * result. Shared by selectResult() (user click) and rehydrateFromUrl()
     * (deep link / refresh) so both paths behave identically.
     */
    protected function loadDetails(): void
    {
        $tmdbId = $this->selectedTmdbId;
        $mediaType = $this->selectedMediaType;

        if ($tmdbId === null || $mediaType === null) {
            return;
        }

        $tmdb = app(TmdbService::class);

        if ($mediaType === 'movie') {
            $details = $tmdb->fetchMovieDetails($tmdbId);
            if (! $details) {
                session()->flash('error', 'Could not fetch movie details from TMDB.');
                $this->clearSelection();

                return;
            }

            $this->title = $this->strOf($details['title'] ?? null);
            $this->original_title = $this->strOf($details['original_title'] ?? null);
            $this->director = $this->strOf($details['director'] ?? null);
            $this->year = $this->intOrNull($details['year'] ?? null);
            $this->runtime_minutes = $this->intOrNull($details['runtime_minutes'] ?? null);
            $this->genres = $this->strOf($details['genres'] ?? null);
            $this->description = $this->strOf($details['description'] ?? null);
            $this->poster_url = $this->strOf($details['poster_url'] ?? null);
            $this->imdb_id = $this->strOf($details['imdb_id'] ?? null);
            $this->status = 'watchlist';
            $this->rating = null;
        } else {
            $details = $tmdb->fetchTVSeasons($tmdbId);
            if (! $details) {
                session()->flash('error', 'Could not fetch TV show details from TMDB.');
                $this->clearSelection();

                return;
            }

            $this->showData = $details;
            $this->seasons = is_array($details['seasons'] ?? null) ? $details['seasons'] : [];
            $this->title = $this->strOf($details['title'] ?? null);
            $this->original_title = $this->strOf($details['original_title'] ?? null);
            $this->genres = $this->strOf($details['genres'] ?? null);
            $this->description = $this->strOf($details['description'] ?? null);
            $this->poster_url = $this->strOf($details['poster_url'] ?? null);
            $this->imdb_id = $this->strOf($details['imdb_id'] ?? null);
            $this->director = $this->strOf($details['director'] ?? null);
            $this->runtime_minutes = $this->intOrNull($details['runtime_minutes'] ?? null);
            $this->year = $this->intOrNull($details['year'] ?? null);
            $this->status = 'watchlist';
            $this->rating = null;
            $this->loadedEpisodes = [];
            $this->selectedEpisodes = [];
            $this->watchedEpisodes = [];
        }
    }

    protected function clearSelection(): void
    {
        $this->selectedTmdbId = null;
        $this->selectedMediaType = null;
    }

    public function addMovie(): void
    {
        $data = [
            'user_id' => Auth::id(),
            'title' => $this->title,
            'original_title' => $this->original_title ?: null,
            'director' => $this->director ?: null,
            'year' => $this->year,
            'runtime_minutes' => $this->runtime_minutes,
            'genres' => $this->genres ?: null,
            'description' => $this->description ?: null,
            'poster_url' => $this->poster_url ?: null,
            'imdb_id' => $this->imdb_id ?: null,
            'title_type' => 'Movie',
            'status' => $this->status,
            'rating' => $this->rating,
            'date_added' => now(),
            'metadata_fetched_at' => now(),
        ];

        if ($this->status === 'watched') {
            $data['date_watched'] = now();
        }

        $movie = Movie::create($data);

        session()->flash('message', "Added \"{$this->title}\" to your library.");
        $this->redirect(route('movies.show', $movie));
    }

    public function loadSeasonEpisodes(int $seasonNumber): void
    {
        if (isset($this->loadedEpisodes[$seasonNumber]) || $this->selectedTmdbId === null) {
            return;
        }

        $episodes = app(TmdbService::class)->fetchTVSeasonEpisodes($this->selectedTmdbId, $seasonNumber);

        if ($episodes) {
            $this->loadedEpisodes[$seasonNumber] = $episodes;
        }
    }

    public function loadAllSeasons(): void
    {
        if ($this->selectedTmdbId === null) {
            return;
        }

        $tmdb = app(TmdbService::class);
        foreach ($this->seasons as $season) {
            $seasonNumber = is_array($season) ? $this->intOrNull($season['season_number'] ?? null) : null;
            if ($seasonNumber === null) {
                continue;
            }
            if (isset($this->loadedEpisodes[$seasonNumber])) {
                continue;
            }
            $episodes = $tmdb->fetchTVSeasonEpisodes($this->selectedTmdbId, $seasonNumber);
            if ($episodes) {
                $this->loadedEpisodes[$seasonNumber] = $episodes;
            }
        }
    }

    public function selectAllEpisodes(): void
    {
        if ($this->loadedEpisodes === []) {
            $this->loadAllSeasons();
        }

        foreach ($this->loadedEpisodes as $seasonNum => $episodes) {
            foreach ($episodes as $ep) {
                $key = $this->episodeKey($seasonNum, $ep);
                if ($key !== null) {
                    $this->selectedEpisodes[$key] = true;
                }
            }
        }
    }

    public function toggleEpisode(int $seasonNumber, int $episodeNumber): void
    {
        $key = "S{$seasonNumber}E{$episodeNumber}";
        if (isset($this->selectedEpisodes[$key])) {
            unset($this->selectedEpisodes[$key]);
            unset($this->watchedEpisodes[$key]);
        } else {
            $this->selectedEpisodes[$key] = true;
        }
    }

    public function toggleEpisodeWatched(int $seasonNumber, int $episodeNumber): void
    {
        $key = "S{$seasonNumber}E{$episodeNumber}";
        if (! isset($this->selectedEpisodes[$key])) {
            $this->selectedEpisodes[$key] = true;
        }
        if (isset($this->watchedEpisodes[$key])) {
            unset($this->watchedEpisodes[$key]);
        } else {
            $this->watchedEpisodes[$key] = true;
        }
    }

    public function selectAllSeason(int $seasonNumber): void
    {
        $episodes = $this->loadedEpisodes[$seasonNumber] ?? [];
        $allSelected = $this->isSeasonFullySelected($seasonNumber);

        foreach ($episodes as $ep) {
            $key = $this->episodeKey($seasonNumber, $ep);
            if ($key === null) {
                continue;
            }
            if ($allSelected) {
                unset($this->selectedEpisodes[$key]);
                unset($this->watchedEpisodes[$key]);
            } else {
                $this->selectedEpisodes[$key] = true;
            }
        }
    }

    public function markSeasonWatched(int $seasonNumber): void
    {
        foreach ($this->loadedEpisodes[$seasonNumber] ?? [] as $ep) {
            $key = $this->episodeKey($seasonNumber, $ep);
            if ($key !== null) {
                $this->selectedEpisodes[$key] = true;
                $this->watchedEpisodes[$key] = true;
            }
        }
    }

    public function markSeasonWatchlist(int $seasonNumber): void
    {
        foreach ($this->loadedEpisodes[$seasonNumber] ?? [] as $ep) {
            $key = $this->episodeKey($seasonNumber, $ep);
            if ($key === null) {
                continue;
            }
            $this->selectedEpisodes[$key] = true;
            if (isset($this->watchedEpisodes[$key])) {
                unset($this->watchedEpisodes[$key]);
            }
        }
    }

    public function isSeasonFullySelected(int $seasonNumber): bool
    {
        $episodes = $this->loadedEpisodes[$seasonNumber] ?? [];
        if (empty($episodes)) {
            return false;
        }
        foreach ($episodes as $ep) {
            $key = $this->episodeKey($seasonNumber, $ep);
            if ($key === null || ! isset($this->selectedEpisodes[$key])) {
                return false;
            }
        }

        return true;
    }

    public function isEpisodeDuplicate(int $seasonNumber, int $episodeNumber): bool
    {
        $key = $this->title.'|'.$seasonNumber.'|'.$episodeNumber;

        return in_array($key, $this->existingEpisodeKeys);
    }

    /**
     * @return array{selected: int, watched: int, watchlist: int}
     */
    public function getSelectionSummaryProperty(): array
    {
        $selected = count($this->selectedEpisodes);
        $watched = count($this->watchedEpisodes);
        $watchlist = $selected - $watched;

        return [
            'selected' => $selected,
            'watched' => $watched,
            'watchlist' => $watchlist,
        ];
    }

    public function importTVShow(): void
    {
        if ($this->selectedEpisodes === []) {
            session()->flash('error', 'No episodes selected.');

            return;
        }

        $userId = (int) Auth::id();
        $showName = $this->title;
        $posterUrl = $this->poster_url ?: null;
        $now = now();
        $imported = 0;
        $skipped = 0;

        DB::transaction(function () use ($userId, $showName, $posterUrl, $now, &$imported, &$skipped): void {
            // Create parent show entry if not already present
            $existingShow = Movie::where('user_id', $userId)
                ->where('title_type', 'TV Series')
                ->where(function ($q) use ($showName): void {
                    $q->where('show_name', $showName)
                        ->orWhere('title', $showName);
                })
                ->first();

            if (! $existingShow) {
                Movie::create([
                    'user_id' => $userId,
                    'title' => $showName,
                    'original_title' => $this->original_title ?: null,
                    'title_type' => 'TV Series',
                    'show_name' => $showName,
                    'director' => $this->director ?: null,
                    'year' => $this->year,
                    'runtime_minutes' => $this->runtime_minutes,
                    'genres' => $this->genres ?: null,
                    'description' => $this->description ?: null,
                    'poster_url' => $posterUrl,
                    'imdb_id' => $this->imdb_id ?: null,
                    'status' => $this->status,
                    'rating' => $this->rating,
                    'date_added' => $now,
                    'metadata_fetched_at' => $now,
                ]);
            }

            // Create episode entries
            foreach (array_keys($this->selectedEpisodes) as $key) {
                if (! preg_match('/^S(\d+)E(\d+)$/', $key, $m)) {
                    continue;
                }
                $seasonNum = (int) $m[1];
                $episodeNum = (int) $m[2];

                // Skip duplicates
                $dupKey = $showName.'|'.$seasonNum.'|'.$episodeNum;
                if (in_array($dupKey, $this->existingEpisodeKeys)) {
                    $skipped++;

                    continue;
                }

                // Find episode data from loaded episodes
                $epData = null;
                foreach ($this->loadedEpisodes[$seasonNum] ?? [] as $ep) {
                    if (($ep['episode_number'] ?? null) === $episodeNum) {
                        $epData = $ep;
                        break;
                    }
                }

                $episodeName = is_string($epData['name'] ?? null) ? $epData['name'] : "Episode {$episodeNum}";
                $isWatched = isset($this->watchedEpisodes[$key]);

                Movie::create([
                    'user_id' => $userId,
                    'title' => "{$showName}: {$episodeName}",
                    'title_type' => 'TV Episode',
                    'show_name' => $showName,
                    'season_number' => $seasonNum,
                    'episode_number' => $episodeNum,
                    'poster_url' => $posterUrl,
                    'genres' => $this->genres ?: null,
                    'description' => $epData['overview'] ?? null,
                    'runtime_minutes' => $epData['runtime_minutes'] ?? null,
                    'status' => $isWatched ? 'watched' : 'watchlist',
                    'date_watched' => $isWatched ? $now : null,
                    'date_added' => $now,
                    'metadata_fetched_at' => $now,
                ]);

                $imported++;
            }

            // Propagate poster to any stragglers
            if ($posterUrl) {
                Movie::propagateShowPoster($userId, $showName, $showName, $posterUrl, $showName);
            }
        });

        $message = "Imported {$imported} episode(s) of \"{$showName}\".";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} duplicate(s).";
        }

        session()->flash('message', $message);
        $this->redirect(route('movies.index'));
    }

    public function backToSearch(): void
    {
        // Clearing the query is the single history-affecting change; step()
        // then derives 'search'.
        $this->searchResults = [];
        $this->query = '';
        $this->currentPage = 1;
        $this->clearSelection();
    }

    public function backToResults(): void
    {
        // Clearing the selection is the single history-affecting change; step()
        // then derives 'results'.
        $this->clearSelection();
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function isResultInLibrary(array $result): bool
    {
        // Can't check without imdb_id at search result level - will show as available
        return false;
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $summary = $this->getSelectionSummaryProperty();

        return view('livewire.movies.movie-tmdb-search', [
            'statuses' => WatchingStatus::cases(),
            'summary' => $summary,
            'step' => $this->step(),
        ]);
    }

    /**
     * Build the "S{season}E{episode}" key from a loaded-episode payload.
     *
     * @param  array<string, mixed>  $ep
     */
    private function episodeKey(int $seasonNumber, array $ep): ?string
    {
        $number = $ep['episode_number'] ?? null;

        return is_numeric($number) ? 'S'.$seasonNumber.'E'.(int) $number : null;
    }

    private function strOf(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
