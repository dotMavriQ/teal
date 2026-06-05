<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\Tmdb\Requests\FindExternalId;
use App\Services\Saloon\Tmdb\Requests\GetMovieDetails;
use App\Services\Saloon\Tmdb\Requests\GetTvDetails;
use App\Services\Saloon\Tmdb\Requests\SearchMulti;
use App\Services\Saloon\Tmdb\TmdbConnector;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected const IMAGE_BASE_URL = 'https://image.tmdb.org/t/p/w500';

    protected const GENRE_MAP = [
        'Action & Adventure' => 'Action, Adventure',
        'Sci-Fi & Fantasy' => 'Sci-Fi, Fantasy',
        'War & Politics' => 'War',
        'Science Fiction' => 'Sci-Fi',
    ];

    protected TmdbConnector $connector;

    public function __construct()
    {
        $this->connector = new TmdbConnector;
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.tmdb.api_key')) || ! empty(config('services.tmdb.access_token'));
    }

    /**
     * Find a movie by its IMDb ID.
     *
     * @return array<string, mixed>|null
     */
    public function findByImdbId(string $imdbId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->connector->send(new FindExternalId($imdbId));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            // Try movie_results first, then tv_results
            $results = is_array($data['movie_results'] ?? null) ? $data['movie_results'] : [];
            $isTV = false;
            if ($results === []) {
                $results = is_array($data['tv_results'] ?? null) ? $data['tv_results'] : [];
                $isTV = true;
            }

            $item = $results[0] ?? null;
            $id = is_array($item) ? $this->toInt($item['id'] ?? null) : null;
            if ($id === null) {
                return null;
            }

            return $isTV ? $this->fetchTVDetails($id) : $this->fetchMovieDetails($id);
        } catch (Exception $e) {
            Log::warning('TMDB API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Search for a movie by title and optional year.
     * Note: Keeping manual HTTP for now to avoid creating too many requests at once.
     *
     * @return array<string, mixed>|null
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

            // Using the connector's helper to still benefit from its auth/base_url
            $response = Http::withHeaders($this->connector->defaultHeaders())
                ->get($this->connector->resolveBaseUrl().'/search/movie', array_merge($this->connector->defaultQuery(), $params));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $results = is_array($data) && is_array($data['results'] ?? null) ? $data['results'] : [];
            $first = $results[0] ?? null;
            $id = is_array($first) ? $this->toInt($first['id'] ?? null) : null;

            return $id !== null ? $this->fetchMovieDetails($id) : null;
        } catch (Exception $e) {
            Log::warning('TMDB API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Fetch full movie details including credits.
     *
     * @return array<string, mixed>|null
     */
    public function fetchMovieDetails(int $tmdbId): ?array
    {
        try {
            $response = $this->connector->send(new GetMovieDetails($tmdbId));

            if (! $response->successful()) {
                return null;
            }

            return $this->normalizeData($response->json());
        } catch (Exception $e) {
            Log::warning('TMDB API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Fetch full TV series details including credits.
     *
     * @return array<string, mixed>|null
     */
    public function fetchTVDetails(int $tmdbId): ?array
    {
        try {
            $response = $this->connector->send(new GetTvDetails($tmdbId));

            if (! $response->successful()) {
                return null;
            }

            return $this->normalizeData($response->json());
        } catch (Exception $e) {
            Log::warning('TMDB API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Find full episode details using an episode's IMDb ID.
     *
     * @return array<string, mixed>|null
     */
    public function findEpisodeDetailsByImdbId(string $imdbId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->connector->send(new FindExternalId($imdbId));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $episodeResults = is_array($data['tv_episode_results'] ?? null) ? $data['tv_episode_results'] : [];
            $episode = $episodeResults[0] ?? null;
            if (! is_array($episode)) {
                return null;
            }

            $showId = $this->toInt($episode['show_id'] ?? null);
            if ($showId === null) {
                return null;
            }

            // Fetch parent show for name and poster
            $showResponse = $this->connector->send(new GetTvDetails($showId, []));
            $showData = $showResponse->successful() ? $showResponse->json() : [];

            return [
                'show_name' => $showData['name'] ?? null,
                'season_number' => $episode['season_number'] ?? null,
                'episode_number' => $episode['episode_number'] ?? null,
                'poster_url' => $this->imageUrl($showData['poster_path'] ?? null),
            ];
        } catch (Exception $e) {
            Log::warning('TMDB API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Search for a TV show by title and return its poster URL.
     */
    public function searchTVShowPosterByTitle(string $title): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->connector->defaultHeaders())
                ->get($this->connector->resolveBaseUrl().'/search/tv', array_merge($this->connector->defaultQuery(), ['query' => $title]));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $results = is_array($data) && is_array($data['results'] ?? null) ? $data['results'] : [];
            $first = $results[0] ?? null;
            $posterPath = is_array($first) ? ($first['poster_path'] ?? null) : null;

            return $this->imageUrl($posterPath);
        } catch (Exception $e) {
            Log::warning('TMDB API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Normalize TMDB response to our field names.
     *
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeData(array $data): array
    {
        $runtime = null;
        if (is_numeric($data['runtime'] ?? null)) {
            $runtime = (int) $data['runtime'];
        } elseif (is_array($data['episode_run_time'] ?? null) && is_numeric($data['episode_run_time'][0] ?? null)) {
            $runtime = (int) $data['episode_run_time'][0];
        }

        $dateStr = $data['release_date'] ?? $data['first_air_date'] ?? null;
        $year = $this->yearFromDate($dateStr);

        $externalIds = $data['external_ids'] ?? null;
        $imdbId = $data['imdb_id'] ?? (is_array($externalIds) ? ($externalIds['imdb_id'] ?? null) : null);

        return [
            'title' => $data['title'] ?? $data['name'] ?? null,
            'original_title' => $data['original_title'] ?? $data['original_name'] ?? null,
            'year' => $year ?: null,
            'imdb_id' => $imdbId,
            'description' => empty($data['overview']) ? null : $data['overview'],
            'poster_url' => $this->imageUrl($data['poster_path'] ?? null),
            'runtime_minutes' => $runtime,
            'release_date' => empty($data['release_date']) ? $data['first_air_date'] ?? null : ($data['release_date']),
            'genres' => $this->extractGenres($data['genres'] ?? null),
            'director' => $this->extractDirector($data['credits'] ?? null),
        ];
    }

    /**
     * Search for movies and TV shows by query.
     *
     * @return array<string, mixed>
     */
    public function searchMulti(string $query, int $page = 1): array
    {
        if (! $this->isConfigured()) {
            return ['results' => [], 'total_pages' => 0, 'total_results' => 0];
        }

        try {
            $response = $this->connector->send(new SearchMulti($query, $page));

            if (! $response->successful()) {
                return ['results' => [], 'total_pages' => 0, 'total_results' => 0];
            }

            $data = $response->json();

            $results = [];
            foreach (is_array($data['results'] ?? null) ? $data['results'] : [] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $mediaType = $item['media_type'] ?? '';
                if (! in_array($mediaType, ['movie', 'tv'], true)) {
                    continue;
                }
                $isTV = $mediaType === 'tv';
                $dateStr = $isTV ? ($item['first_air_date'] ?? null) : ($item['release_date'] ?? null);

                $results[] = [
                    'tmdb_id' => $item['id'] ?? null,
                    'media_type' => $mediaType,
                    'title' => $isTV ? ($item['name'] ?? '') : ($item['title'] ?? ''),
                    'year' => $this->yearFromDate($dateStr) ?: null,
                    'poster_url' => $this->imageUrl($item['poster_path'] ?? null),
                    'overview' => $item['overview'] ?? null,
                ];
            }

            return [
                'results' => $results,
                'total_pages' => $data['total_pages'] ?? 0,
                'total_results' => $data['total_results'] ?? 0,
            ];
        } catch (Exception $e) {
            Log::warning('TMDB API error: '.$e->getMessage());

            return ['results' => [], 'total_pages' => 0, 'total_results' => 0];
        }
    }

    /**
     * Fetch TV show details with seasons list.
     *
     * @return array<string, mixed>|null
     */
    public function fetchTVSeasons(int $tmdbId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->connector->send(new GetTvDetails($tmdbId));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $normalized = $this->normalizeData($data);

            $seasons = [];
            foreach (is_array($data['seasons'] ?? null) ? $data['seasons'] : [] as $s) {
                if (! is_array($s)) {
                    continue;
                }
                $num = $this->toInt($s['season_number'] ?? null);
                if ($num === null) {
                    continue;
                }
                if ($num <= 0) {
                    continue;
                }
                $seasons[] = [
                    'season_number' => $num,
                    'name' => is_string($s['name'] ?? null) ? $s['name'] : "Season {$num}",
                    'episode_count' => $s['episode_count'] ?? 0,
                    'poster_url' => $this->imageUrl($s['poster_path'] ?? null),
                ];
            }

            $normalized['seasons'] = $seasons;

            return $normalized;
        } catch (Exception $e) {
            Log::warning('TMDB API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Fetch episodes for a specific TV season.
     *
     * @return list<array<string, mixed>>|null
     */
    public function fetchTVSeasonEpisodes(int $tmdbId, int $seasonNumber): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->connector->defaultHeaders())
                ->get($this->connector->resolveBaseUrl().'/tv/'.$tmdbId.'/season/'.$seasonNumber, $this->connector->defaultQuery());

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            $episodes = [];
            foreach (is_array($data) && is_array($data['episodes'] ?? null) ? $data['episodes'] : [] as $ep) {
                if (! is_array($ep)) {
                    continue;
                }
                $epNum = $this->toInt($ep['episode_number'] ?? null);
                $episodes[] = [
                    'episode_number' => $ep['episode_number'] ?? null,
                    'name' => is_string($ep['name'] ?? null) ? $ep['name'] : 'Episode '.($epNum ?? ''),
                    'overview' => $ep['overview'] ?? null,
                    'air_date' => $ep['air_date'] ?? null,
                    'runtime_minutes' => is_numeric($ep['runtime'] ?? null) ? (int) $ep['runtime'] : null,
                ];
            }

            return $episodes;
        } catch (Exception $e) {
            Log::warning('TMDB API error: '.$e->getMessage());

            return null;
        }
    }

    protected function extractGenres(mixed $genres): ?string
    {
        if (! is_array($genres) || $genres === []) {
            return null;
        }

        $names = [];
        foreach ($genres as $g) {
            if (! is_array($g)) {
                continue;
            }
            if (! is_string($g['name'] ?? null)) {
                continue;
            }
            $mapped = self::GENRE_MAP[$g['name']] ?? $g['name'];
            foreach (explode(', ', $mapped) as $part) {
                $names[$part] = true;
            }
        }

        return $names !== [] ? implode(', ', array_keys($names)) : null;
    }

    protected function extractDirector(mixed $credits): ?string
    {
        $crew = is_array($credits) ? ($credits['crew'] ?? null) : null;

        $directors = [];
        foreach (is_array($crew) ? $crew : [] as $person) {
            if (is_array($person) && ($person['job'] ?? '') === 'Director' && is_string($person['name'] ?? null)) {
                $directors[] = $person['name'];
            }
        }

        return $directors !== [] ? implode(', ', $directors) : null;
    }

    protected function imageUrl(mixed $path): ?string
    {
        return is_string($path) && $path !== '' ? self::IMAGE_BASE_URL.$path : null;
    }

    protected function toInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    protected function yearFromDate(mixed $date): ?int
    {
        return is_string($date) && $date !== '' ? (int) substr($date, 0, 4) : null;
    }
}
