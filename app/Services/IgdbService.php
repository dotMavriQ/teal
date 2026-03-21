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
        $this->connector = new IgdbConnector();
    }

    public function search(string $query, int $page = 1, int $perPage = 20, ?int $platformId = null): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $response = $this->connector->send(new SearchGames($query, $perPage, $offset, $platformId));

            if (! $response->successful()) {
                return ['results' => [], 'total' => 0, 'total_pages' => 0];
            }

            $games = $response->json() ?? [];

            $results = array_map(fn (array $game) => $this->normalizeGame($game), $games);

            return [
                'results' => $results,
                'total' => count($results),
                'total_pages' => count($results) < $perPage ? $page : $page + 1,
            ];
        } catch (\Exception) {
            return ['results' => [], 'total' => 0, 'total_pages' => 0];
        }
    }

    public function getDetails(int $igdbId): ?array
    {
        try {
            $response = $this->connector->send(new GetGameDetails($igdbId));

            if (! $response->successful()) {
                return null;
            }

            $games = $response->json() ?? [];

            return ! empty($games) ? $this->normalizeGame($games[0]) : null;
        } catch (\Exception) {
            return null;
        }
    }

    protected function normalizeGame(array $game): array
    {
        $coverUrl = null;
        if (isset($game['cover']['url'])) {
            // IGDB returns //images.igdb.com/... — upgrade to https and get bigger image
            $coverUrl = str_replace('t_thumb', 't_cover_big', $game['cover']['url']);
            if (str_starts_with($coverUrl, '//')) {
                $coverUrl = 'https:' . $coverUrl;
            }
        }

        $developer = null;
        $publisher = null;
        foreach ($game['involved_companies'] ?? [] as $company) {
            $name = $company['company']['name'] ?? null;
            if ($name && ! empty($company['developer'])) {
                $developer = $name;
            }
            if ($name && ! empty($company['publisher'])) {
                $publisher = $name;
            }
        }

        $platforms = [];
        foreach ($game['platforms'] ?? [] as $platform) {
            if (isset($platform['name'])) {
                $platforms[] = $platform['name'];
            }
        }

        $genres = [];
        foreach ($game['genres'] ?? [] as $genre) {
            if (isset($genre['name'])) {
                $genres[] = $genre['name'];
            }
        }

        $releaseDate = null;
        if (isset($game['first_release_date'])) {
            $releaseDate = date('Y-m-d', $game['first_release_date']);
        }

        return [
            'igdb_id' => $game['id'],
            'title' => $game['name'] ?? 'Unknown',
            'summary' => $game['summary'] ?? null,
            'cover_url' => $coverUrl,
            'developer' => $developer,
            'publisher' => $publisher,
            'platforms' => $platforms,
            'genres' => $genres,
            'release_date' => $releaseDate,
            'rating' => isset($game['total_rating']) ? round($game['total_rating'] / 10, 1) : null,
            'source' => 'igdb',
        ];
    }
}
