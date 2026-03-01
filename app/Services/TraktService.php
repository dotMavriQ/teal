<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\Trakt\Requests\SearchByImdbId;
use App\Services\Saloon\Trakt\Requests\SearchText;
use App\Services\Saloon\Trakt\TraktConnector;

class TraktService
{
    protected TraktConnector $connector;

    public function __construct()
    {
        $this->connector = new TraktConnector();
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.trakt.client_id'));
    }

    /**
     * Find a movie or show by its IMDb ID.
     */
    public function findByImdbId(string $imdbId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->connector->send(new SearchByImdbId($imdbId));

            if (! $response->successful()) {
                return null;
            }

            $results = $response->json();

            if (empty($results)) {
                return null;
            }

            $item = $results[0];
            $type = $item['type'] ?? 'movie';
            $data = $item[$type] ?? null;

            if (! $data) {
                return null;
            }

            return $this->normalizeData($data, $type);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Search by title. The $year parameter is accepted for interface
     * compatibility with TmdbService but not used by Trakt's search.
     */
    public function searchByTitle(string $title, mixed $year = null): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->connector->send(new SearchText($title));

            if (! $response->successful()) {
                return null;
            }

            $results = $response->json();

            if (empty($results)) {
                return null;
            }

            $item = $results[0];
            $itemType = $item['type'] ?? 'movie';
            $data = $item[$itemType] ?? null;

            if (! $data) {
                return null;
            }

            return $this->normalizeData($data, $itemType);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Normalize Trakt response to match the field names used by TmdbService.
     */
    protected function normalizeData(array $data, string $type): array
    {
        $runtime = ! empty($data['runtime']) ? (int) $data['runtime'] : null;

        $dateStr = $type === 'movie'
            ? ($data['released'] ?? null)
            : ($data['first_aired'] ?? null);

        // first_aired is ISO 8601, extract just the date part
        $releaseDate = null;
        if ($dateStr) {
            $releaseDate = substr($dateStr, 0, 10);
        }

        $year = $releaseDate ? (int) substr($releaseDate, 0, 4) : ($data['year'] ?? null);

        $genres = ! empty($data['genres'])
            ? collect($data['genres'])->map(fn (string $g) => ucfirst($g))->implode(', ')
            : null;

        return [
            'title' => $data['title'] ?? null,
            'original_title' => $data['original_title'] ?? null,
            'year' => $year ?: null,
            'description' => ! empty($data['overview']) ? $data['overview'] : null,
            'poster_url' => $this->extractPosterUrl($data),
            'runtime_minutes' => $runtime,
            'release_date' => $releaseDate,
            'genres' => $genres,
            'director' => null, // Trakt doesn't include crew in search/summary
        ];
    }

    /**
     * Extract poster URL from Trakt images if available.
     */
    protected function extractPosterUrl(array $data): ?string
    {
        $posters = $data['images']['poster'] ?? [];

        if (empty($posters)) {
            return null;
        }

        $posterPath = $posters[0];

        // Trakt returns relative paths like "media.trakt.tv/images/..."
        if (! str_starts_with($posterPath, 'http')) {
            $posterPath = 'https://' . $posterPath;
        }

        return $posterPath;
    }
}
