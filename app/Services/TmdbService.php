<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService
{
    protected const BASE_URL = 'https://api.themoviedb.org/3';

    protected const IMAGE_BASE_URL = 'https://image.tmdb.org/t/p/w500';

    protected const TIMEOUT = 30;

    protected ?string $apiKey;

    protected ?string $accessToken;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
        $this->accessToken = config('services.tmdb.access_token');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) || ! empty($this->accessToken);
    }

    /**
     * Build an HTTP request with the appropriate auth method.
     * Prefers bearer token if available, falls back to API key.
     */
    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::timeout(self::TIMEOUT);

        if (! empty($this->accessToken)) {
            return $request->withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'accept' => 'application/json',
            ]);
        }

        return $request;
    }

    /**
     * Add API key to params if using key-based auth (no bearer token).
     */
    protected function authParams(array $params = []): array
    {
        if (empty($this->accessToken) && ! empty($this->apiKey)) {
            $params['api_key'] = $this->apiKey;
        }

        return $params;
    }

    /**
     * Find a movie by its IMDb ID.
     * Checks both movie_results and tv_results since the ID could be for either.
     */
    public function findByImdbId(string $imdbId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->request()
                ->get(self::BASE_URL . '/find/' . $imdbId, $this->authParams([
                    'external_source' => 'imdb_id',
                ]));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            // Try movie_results first
            $results = $data['movie_results'] ?? [];
            $isTV = false;

            // If no movie result, try tv_results
            if (empty($results)) {
                $results = $data['tv_results'] ?? [];
                $isTV = true;
            }

            if (empty($results)) {
                return null;
            }

            $item = $results[0];

            // Fetch full details (use tv endpoint if it's a TV series)
            return $isTV ? $this->fetchTVDetails($item['id']) : $this->fetchMovieDetails($item['id']);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Search for a movie by title and optional year.
     */
    public function searchByTitle(string $title, ?int $year = null): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $params = ['query' => $title];

            if ($year) {
                $params['year'] = $year;
            }

            $response = $this->request()
                ->get(self::BASE_URL . '/search/movie', $this->authParams($params));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $results = $data['results'] ?? [];

            if (empty($results)) {
                return null;
            }

            // Use the first (most relevant) result
            return $this->fetchMovieDetails($results[0]['id']);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Fetch full movie details including credits.
     */
    public function fetchMovieDetails(int $tmdbId): ?array
    {
        try {
            $response = $this->request()
                ->get(self::BASE_URL . '/movie/' . $tmdbId, $this->authParams([
                    'append_to_response' => 'credits',
                ]));

            if (! $response->successful()) {
                return null;
            }

            return $this->normalizeData($response->json());
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Fetch full TV series details including credits.
     */
    public function fetchTVDetails(int $tmdbId): ?array
    {
        try {
            $response = $this->request()
                ->get(self::BASE_URL . '/tv/' . $tmdbId, $this->authParams([
                    'append_to_response' => 'credits,external_ids',
                ]));

            if (! $response->successful()) {
                return null;
            }

            return $this->normalizeData($response->json());
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Find full episode details using an episode's IMDb ID.
     * Returns show_name, season_number, episode_number, and poster_url from the parent show.
     */
    public function findEpisodeDetailsByImdbId(string $imdbId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->request()
                ->get(self::BASE_URL . '/find/' . $imdbId, $this->authParams([
                    'external_source' => 'imdb_id',
                ]));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $episodeResults = $data['tv_episode_results'] ?? [];

            if (empty($episodeResults)) {
                return null;
            }

            $episode = $episodeResults[0];
            $showId = $episode['show_id'] ?? null;

            if (! $showId) {
                return null;
            }

            // Fetch parent show for name and poster
            $showResponse = $this->request()
                ->get(self::BASE_URL . '/tv/' . $showId, $this->authParams());

            $showData = $showResponse->successful() ? $showResponse->json() : [];

            return [
                'show_name' => $showData['name'] ?? null,
                'season_number' => $episode['season_number'] ?? null,
                'episode_number' => $episode['episode_number'] ?? null,
                'poster_url' => ! empty($showData['poster_path'])
                    ? self::IMAGE_BASE_URL . $showData['poster_path']
                    : null,
            ];
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Search for a TV show by title and return its poster URL.
     * Uses /search/tv (not /search/movie) for more accurate TV results.
     */
    public function searchTVShowPosterByTitle(string $title): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->request()
                ->get(self::BASE_URL . '/search/tv', $this->authParams([
                    'query' => $title,
                ]));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $results = $data['results'] ?? [];

            if (empty($results)) {
                return null;
            }

            $posterPath = $results[0]['poster_path'] ?? null;

            return $posterPath ? self::IMAGE_BASE_URL . $posterPath : null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Normalize TMDB response to our field names.
     */
    protected function normalizeData(array $data): array
    {
        // Handle TV runtime (episode_run_time array) vs movie runtime (int)
        $runtime = null;
        if (! empty($data['runtime'])) {
            $runtime = (int) $data['runtime'];
        } elseif (! empty($data['episode_run_time']) && is_array($data['episode_run_time'])) {
            $runtime = (int) $data['episode_run_time'][0];
        }

        // Extract year from release_date (movie) or first_air_date (TV)
        $dateStr = $data['release_date'] ?? $data['first_air_date'] ?? null;
        $year = $dateStr ? (int) substr($dateStr, 0, 4) : null;

        return [
            'title' => $data['title'] ?? $data['name'] ?? null,
            'original_title' => $data['original_title'] ?? $data['original_name'] ?? null,
            'year' => $year ?: null,
            'imdb_id' => $data['imdb_id'] ?? ($data['external_ids']['imdb_id'] ?? null),
            'description' => ! empty($data['overview']) ? $data['overview'] : null,
            'poster_url' => ! empty($data['poster_path'])
                ? self::IMAGE_BASE_URL . $data['poster_path']
                : null,
            'runtime_minutes' => $runtime,
            'release_date' => ! empty($data['release_date']) ? $data['release_date'] : ($data['first_air_date'] ?? null),
            'genres' => $this->extractGenres($data['genres'] ?? []),
            'director' => $this->extractDirector($data['credits'] ?? []),
        ];
    }

    /**
     * Search for movies and TV shows by query.
     */
    public function searchMulti(string $query, int $page = 1): array
    {
        if (! $this->isConfigured()) {
            return ['results' => [], 'total_pages' => 0, 'total_results' => 0];
        }

        try {
            $response = $this->request()
                ->get(self::BASE_URL . '/search/multi', $this->authParams([
                    'query' => $query,
                    'page' => $page,
                ]));

            if (! $response->successful()) {
                return ['results' => [], 'total_pages' => 0, 'total_results' => 0];
            }

            $data = $response->json();

            $results = collect($data['results'] ?? [])
                ->filter(fn ($item) => in_array($item['media_type'] ?? '', ['movie', 'tv']))
                ->map(function ($item) {
                    $isTV = ($item['media_type'] ?? '') === 'tv';
                    $dateStr = $isTV ? ($item['first_air_date'] ?? null) : ($item['release_date'] ?? null);
                    $year = $dateStr ? (int) substr($dateStr, 0, 4) : null;

                    return [
                        'tmdb_id' => $item['id'],
                        'media_type' => $item['media_type'],
                        'title' => $isTV ? ($item['name'] ?? '') : ($item['title'] ?? ''),
                        'year' => $year ?: null,
                        'poster_url' => ! empty($item['poster_path'])
                            ? self::IMAGE_BASE_URL . $item['poster_path']
                            : null,
                        'overview' => $item['overview'] ?? null,
                    ];
                })
                ->values()
                ->all();

            return [
                'results' => $results,
                'total_pages' => $data['total_pages'] ?? 0,
                'total_results' => $data['total_results'] ?? 0,
            ];
        } catch (\Exception) {
            return ['results' => [], 'total_pages' => 0, 'total_results' => 0];
        }
    }

    /**
     * Fetch TV show details with seasons list.
     */
    public function fetchTVSeasons(int $tmdbId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->request()
                ->get(self::BASE_URL . '/tv/' . $tmdbId, $this->authParams([
                    'append_to_response' => 'credits,external_ids',
                ]));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $normalized = $this->normalizeData($data);

            $seasons = collect($data['seasons'] ?? [])
                ->filter(fn ($s) => ($s['season_number'] ?? 0) > 0) // Filter out Season 0 (specials)
                ->map(fn ($s) => [
                    'season_number' => $s['season_number'],
                    'name' => $s['name'] ?? "Season {$s['season_number']}",
                    'episode_count' => $s['episode_count'] ?? 0,
                    'poster_url' => ! empty($s['poster_path'])
                        ? self::IMAGE_BASE_URL . $s['poster_path']
                        : null,
                ])
                ->values()
                ->all();

            $normalized['seasons'] = $seasons;

            return $normalized;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Fetch episodes for a specific TV season.
     */
    public function fetchTVSeasonEpisodes(int $tmdbId, int $seasonNumber): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->request()
                ->get(self::BASE_URL . '/tv/' . $tmdbId . '/season/' . $seasonNumber, $this->authParams());

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            return collect($data['episodes'] ?? [])
                ->map(fn ($ep) => [
                    'episode_number' => $ep['episode_number'],
                    'name' => $ep['name'] ?? "Episode {$ep['episode_number']}",
                    'overview' => $ep['overview'] ?? null,
                    'air_date' => $ep['air_date'] ?? null,
                    'runtime_minutes' => ! empty($ep['runtime']) ? (int) $ep['runtime'] : null,
                ])
                ->values()
                ->all();
        } catch (\Exception) {
            return null;
        }
    }

    protected const GENRE_MAP = [
        'Action & Adventure' => 'Action, Adventure',
        'Sci-Fi & Fantasy' => 'Sci-Fi, Fantasy',
        'War & Politics' => 'War',
        'Science Fiction' => 'Sci-Fi',
    ];

    protected function extractGenres(array $genres): ?string
    {
        if (empty($genres)) {
            return null;
        }

        return collect($genres)
            ->pluck('name')
            ->map(fn (string $name) => self::GENRE_MAP[$name] ?? $name)
            ->flatMap(fn (string $name) => explode(', ', $name))
            ->unique()
            ->implode(', ');
    }

    protected function extractDirector(array $credits): ?string
    {
        $crew = $credits['crew'] ?? [];

        $directors = collect($crew)
            ->filter(fn ($person) => ($person['job'] ?? '') === 'Director')
            ->pluck('name')
            ->toArray();

        return ! empty($directors) ? implode(', ', $directors) : null;
    }
}
