<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\Trakt\Requests\SearchByImdbId;
use App\Services\Saloon\Trakt\Requests\SearchText;
use App\Services\Saloon\Trakt\TraktConnector;
use Exception;
use Illuminate\Support\Facades\Log;

class TraktService
{
    protected TraktConnector $connector;

    public function __construct()
    {
        $this->connector = new TraktConnector;
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.trakt.client_id'));
    }

    /**
     * Find a movie or show by its IMDb ID.
     *
     * @return array<string, mixed>|null
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

            $item = $response->json()[0] ?? null;

            if (! is_array($item)) {
                return null;
            }

            $type = is_string($item['type'] ?? null) ? $item['type'] : 'movie';
            $data = $item[$type] ?? null;

            if (! is_array($data)) {
                return null;
            }

            return $this->normalizeData($data, $type);
        } catch (Exception $e) {
            Log::warning('Trakt API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Search by title. The $year parameter is accepted for interface
     * compatibility with TmdbService but not used by Trakt's search.
     *
     * @return array<string, mixed>|null
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

            $item = $response->json()[0] ?? null;

            if (! is_array($item)) {
                return null;
            }

            $itemType = is_string($item['type'] ?? null) ? $item['type'] : 'movie';
            $data = $item[$itemType] ?? null;

            if (! is_array($data)) {
                return null;
            }

            return $this->normalizeData($data, $itemType);
        } catch (Exception $e) {
            Log::warning('Trakt API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Normalize Trakt response to match the field names used by TmdbService.
     *
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeData(array $data, string $type): array
    {
        $runtime = is_numeric($data['runtime'] ?? null) ? (int) $data['runtime'] : null;

        $dateStr = $type === 'movie'
            ? ($data['released'] ?? null)
            : ($data['first_aired'] ?? null);

        // first_aired is ISO 8601, extract just the date part
        $releaseDate = is_string($dateStr) && $dateStr !== '' ? substr($dateStr, 0, 10) : null;

        $year = $releaseDate ? (int) substr($releaseDate, 0, 4) : ($data['year'] ?? null);

        $genreList = [];
        foreach (is_array($data['genres'] ?? null) ? $data['genres'] : [] as $g) {
            if (is_string($g)) {
                $genreList[] = ucfirst($g);
            }
        }
        $genres = $genreList !== [] ? implode(', ', $genreList) : null;

        return [
            'title' => $data['title'] ?? null,
            'original_title' => $data['original_title'] ?? null,
            'year' => $year ?: null,
            'description' => empty($data['overview']) ? null : $data['overview'],
            'poster_url' => $this->extractPosterUrl($data),
            'runtime_minutes' => $runtime,
            'release_date' => $releaseDate,
            'genres' => $genres,
            'director' => null, // Trakt doesn't include crew in search/summary
        ];
    }

    /**
     * Extract poster URL from Trakt images if available.
     *
     * @param  array<array-key, mixed>  $data
     */
    protected function extractPosterUrl(array $data): ?string
    {
        $images = $data['images'] ?? null;
        $posters = is_array($images) ? ($images['poster'] ?? null) : null;
        $posterPath = is_array($posters) ? ($posters[0] ?? null) : null;

        if (! is_string($posterPath) || $posterPath === '') {
            return null;
        }

        // Trakt returns relative paths like "media.trakt.tv/images/..."
        if (! str_starts_with($posterPath, 'http')) {
            return 'https://'.$posterPath;
        }

        return $posterPath;
    }
}
