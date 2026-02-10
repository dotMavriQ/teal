<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Enums\WatchingStatus;
use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MovieTmdbSearch extends Component
{
    public string $step = 'search';

    // Search state
    public string $query = '';
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
    public array $showData = [];
    public array $seasons = [];
    public array $loadedEpisodes = []; // season_number => episodes[]
    public array $selectedEpisodes = []; // "S{n}E{n}" => true
    public array $watchedEpisodes = []; // "S{n}E{n}" => true

    // Duplicate detection
    public array $existingImdbIds = [];
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
            ->map(fn ($m) => $m->show_name . '|' . $m->season_number . '|' . $m->episode_number)
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

        $this->searchResults = $result['results'];
        $this->totalPages = $result['total_pages'];
        $this->currentPage = 1;
        $this->step = 'results';
    }

    public function loadPage(int $page): void
    {
        $tmdb = app(TmdbService::class);
        $result = $tmdb->searchMulti(trim($this->query), $page);

        $this->searchResults = $result['results'];
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

            $this->title = $details['title'] ?? '';
            $this->original_title = $details['original_title'] ?? '';
            $this->director = $details['director'] ?? '';
            $this->year = $details['year'];
            $this->runtime_minutes = $details['runtime_minutes'];
            $this->genres = $details['genres'] ?? '';
            $this->description = $details['description'] ?? '';
            $this->poster_url = $details['poster_url'] ?? '';
            $this->imdb_id = $details['imdb_id'] ?? '';
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
            $this->seasons = $details['seasons'] ?? [];
            $this->title = $details['title'] ?? '';
            $this->original_title = $details['original_title'] ?? '';
            $this->genres = $details['genres'] ?? '';
            $this->description = $details['description'] ?? '';
            $this->poster_url = $details['poster_url'] ?? '';
            $this->imdb_id = $details['imdb_id'] ?? '';
            $this->director = $details['director'] ?? '';
            $this->runtime_minutes = $details['runtime_minutes'];
            $this->year = $details['year'];
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
        if (isset($this->loadedEpisodes[$seasonNumber])) {
            return;
        }

        $tmdb = app(TmdbService::class);
        $episodes = $tmdb->fetchTVSeasonEpisodes($this->selectedTmdbId, $seasonNumber);

        if ($episodes) {
            $this->loadedEpisodes[$seasonNumber] = $episodes;
        }
    }

    public function goToSelectEpisodes(): void
    {
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
            $key = "S{$seasonNumber}E{$ep['episode_number']}";
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
        $episodes = $this->loadedEpisodes[$seasonNumber] ?? [];
        foreach ($episodes as $ep) {
            $key = "S{$seasonNumber}E{$ep['episode_number']}";
            $this->selectedEpisodes[$key] = true;
            $this->watchedEpisodes[$key] = true;
        }
    }

    public function isSeasonFullySelected(int $seasonNumber): bool
    {
        $episodes = $this->loadedEpisodes[$seasonNumber] ?? [];
        if (empty($episodes)) {
            return false;
        }
        foreach ($episodes as $ep) {
            $key = "S{$seasonNumber}E{$ep['episode_number']}";
            if (! isset($this->selectedEpisodes[$key])) {
                return false;
            }
        }
        return true;
    }

    public function isEpisodeDuplicate(int $seasonNumber, int $episodeNumber): bool
    {
        $showName = $this->title;
        $key = $showName . '|' . $seasonNumber . '|' . $episodeNumber;
        return in_array($key, $this->existingEpisodeKeys);
    }

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
        if (empty($this->selectedEpisodes)) {
            session()->flash('error', 'No episodes selected.');
            return;
        }

        $userId = Auth::id();
        $showName = $this->title;
        $posterUrl = $this->poster_url ?: null;
        $now = now();
        $imported = 0;
        $skipped = 0;

        DB::transaction(function () use ($userId, $showName, $posterUrl, $now, &$imported, &$skipped) {
            // Create parent show entry if not already present
            $existingShow = Movie::where('user_id', $userId)
                ->where('title_type', 'TV Series')
                ->where(function ($q) use ($showName) {
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
            foreach ($this->selectedEpisodes as $key => $_) {
                if (! preg_match('/^S(\d+)E(\d+)$/', $key, $m)) {
                    continue;
                }
                $seasonNum = (int) $m[1];
                $episodeNum = (int) $m[2];

                // Skip duplicates
                $dupKey = $showName . '|' . $seasonNum . '|' . $episodeNum;
                if (in_array($dupKey, $this->existingEpisodeKeys)) {
                    $skipped++;
                    continue;
                }

                // Find episode data from loaded episodes
                $epData = null;
                foreach ($this->loadedEpisodes[$seasonNum] ?? [] as $ep) {
                    if ($ep['episode_number'] === $episodeNum) {
                        $epData = $ep;
                        break;
                    }
                }

                $episodeName = $epData['name'] ?? "Episode {$episodeNum}";
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

    public function isResultInLibrary(array $result): bool
    {
        // Can't check without imdb_id at search result level - will show as available
        return false;
    }

    public function render()
    {
        $summary = $this->getSelectionSummaryProperty();

        return view('livewire.movies.movie-tmdb-search', [
            'statuses' => WatchingStatus::cases(),
            'summary' => $summary,
        ])->layout('layouts.app');
    }
}
