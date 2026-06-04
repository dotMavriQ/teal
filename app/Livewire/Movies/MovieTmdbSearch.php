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
use Livewire\Component;

class MovieTmdbSearch extends Component
{
    public string $step = 'search';

    // Search state
    public string $query = '';

    /** @var array<array-key, mixed> */
    public array $searchResults = [];

    public int $totalPages = 0;

    public int $currentPage = 1;

    // Selected result details
    public ?string $selectedMediaType = null;

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
        $this->step = 'results';
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

        $tmdb = app(TmdbService::class);

        if ($mediaType === 'movie') {
            $details = $tmdb->fetchMovieDetails($tmdbId);
            if (! $details) {
                session()->flash('error', 'Could not fetch movie details from TMDB.');

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

            $this->step = 'configure_movie';
        } else {
            $details = $tmdb->fetchTVSeasons($tmdbId);
            if (! $details) {
                session()->flash('error', 'Could not fetch TV show details from TMDB.');

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

            $this->step = 'configure_tv';
        }
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

    public function goToSelectEpisodes(): void
    {
        if ($this->loadedEpisodes === []) {
            $this->loadAllSeasons();
        }
        $this->step = 'select_episodes';
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
        $this->step = 'search';
        $this->searchResults = [];
        $this->query = '';
    }

    public function backToResults(): void
    {
        $this->step = 'results';
    }

    public function backToConfigureTV(): void
    {
        $this->step = 'configure_tv';
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
