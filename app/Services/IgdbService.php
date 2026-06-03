<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\Igdb\IgdbConnector;
use App\Services\Saloon\Igdb\Requests\GetGameDetails;
use App\Services\Saloon\Igdb\Requests\SearchGames;

class IgdbService
{
    protected IgdbConnector $connector;

    public function __construct()
    {
        $this->connector = new IgdbConnector;
    }

    /**
     * @return array{results: list<array<string, mixed>>, total: int, total_pages: int}
     */
    public function search(string $query, int $page = 1, int $perPage = 20, ?int $platformId = null): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $response = $this->connector->send(new SearchGames($query, $perPage, $offset, $platformId));

            if (! $response->successful()) {
                return ['results' => [], 'total' => 0, 'total_pages' => 0];
            }

            $results = [];
            foreach ($response->json() as $game) {
                if (is_array($game)) {
                    $results[] = $this->normalizeGame($game);
                }
            }

            return [
                'results' => $results,
                'total' => count($results),
                'total_pages' => count($results) < $perPage ? $page : $page + 1,
            ];
        } catch (\Exception) {
            return ['results' => [], 'total' => 0, 'total_pages' => 0];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDetails(int $igdbId): ?array
    {
        try {
            $response = $this->connector->send(new GetGameDetails($igdbId));

            if (! $response->successful()) {
                return null;
            }

            $first = $response->json()[0] ?? null;

            return is_array($first) ? $this->normalizeGame($first) : null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @param  array<array-key, mixed>  $game
     * @return array<string, mixed>
     */
    protected function normalizeGame(array $game): array
    {
        $coverUrl = null;
        $cover = $game['cover'] ?? null;
        if (is_array($cover) && is_string($cover['url'] ?? null)) {
            // IGDB returns //images.igdb.com/... — upgrade to https and get bigger image
            $coverUrl = str_replace('t_thumb', 't_cover_big', $cover['url']);
            if (str_starts_with($coverUrl, '//')) {
                $coverUrl = 'https:'.$coverUrl;
            }
        }

        $developer = null;
        $publisher = null;
        foreach (is_array($game['involved_companies'] ?? null) ? $game['involved_companies'] : [] as $company) {
            if (! is_array($company)) {
                continue;
            }
            $companyData = $company['company'] ?? null;
            $name = is_array($companyData) && is_string($companyData['name'] ?? null) ? $companyData['name'] : null;
            if ($name !== null && ! empty($company['developer'])) {
                $developer = $name;
            }
            if ($name !== null && ! empty($company['publisher'])) {
                $publisher = $name;
            }
        }

        $platforms = [];
        foreach (is_array($game['platforms'] ?? null) ? $game['platforms'] : [] as $platform) {
            if (is_array($platform) && is_string($platform['name'] ?? null)) {
                $platforms[] = $platform['name'];
            }
        }

        $genres = [];
        foreach (is_array($game['genres'] ?? null) ? $game['genres'] : [] as $genre) {
            if (is_array($genre) && is_string($genre['name'] ?? null)) {
                $genres[] = $genre['name'];
            }
        }

        $releaseDate = null;
        $firstRelease = $game['first_release_date'] ?? null;
        if (is_int($firstRelease)) {
            $releaseDate = date('Y-m-d', $firstRelease);
        }

        $totalRating = $game['total_rating'] ?? null;

        return [
            'igdb_id' => $game['id'] ?? null,
            'title' => is_string($game['name'] ?? null) ? $game['name'] : 'Unknown',
            'summary' => $game['summary'] ?? null,
            'cover_url' => $coverUrl,
            'developer' => $developer,
            'publisher' => $publisher,
            'platforms' => $platforms,
            'genres' => $genres,
            'release_date' => $releaseDate,
            'rating' => is_numeric($totalRating) ? round((float) $totalRating / 10, 1) : null,
            'source' => 'igdb',
        ];
    }
}
