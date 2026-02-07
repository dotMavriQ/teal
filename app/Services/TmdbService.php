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
            $results = $data['movie_results'] ?? [];

            if (empty($results)) {
                return null;
            }

            $movie = $results[0];

            // Fetch full details (includes runtime, credits)
            return $this->fetchMovieDetails($movie['id']);
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
    protected function fetchMovieDetails(int $tmdbId): ?array
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
     * Normalize TMDB response to our field names.
     */
    protected function normalizeData(array $data): array
    {
        return [
            'description' => ! empty($data['overview']) ? $data['overview'] : null,
            'poster_url' => ! empty($data['poster_path'])
                ? self::IMAGE_BASE_URL . $data['poster_path']
                : null,
            'runtime_minutes' => ! empty($data['runtime']) ? (int) $data['runtime'] : null,
            'release_date' => ! empty($data['release_date']) ? $data['release_date'] : null,
            'genres' => $this->extractGenres($data['genres'] ?? []),
            'director' => $this->extractDirector($data['credits'] ?? []),
        ];
    }

    protected function extractGenres(array $genres): ?string
    {
        if (empty($genres)) {
            return null;
        }

        return collect($genres)->pluck('name')->implode(', ');
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
