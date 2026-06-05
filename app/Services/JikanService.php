<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\Jikan\JikanConnector;
use App\Services\Saloon\Jikan\Requests\GetAnimeDetails;
use App\Services\Saloon\Jikan\Requests\SearchAnime;
use Exception;

class JikanService
{
    protected JikanConnector $connector;

    public function __construct()
    {
        $this->connector = new JikanConnector;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByMalId(int $malId): ?array
    {
        try {
            $response = $this->connector->send(new GetAnimeDetails($malId));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json('data');

            return is_array($data) && $data !== [] ? $this->normalizeData($data) : null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function searchByTitle(string $title): ?array
    {
        try {
            $response = $this->connector->send(new SearchAnime($title));

            if (! $response->successful()) {
                return null;
            }

            $results = $response->json('data');
            $first = is_array($results) ? ($results[0] ?? null) : null;

            return is_array($first) ? $this->normalizeData($first) : null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalizeData(array $data): array
    {
        $genres = $this->joinNames($data['genres'] ?? null, $data['themes'] ?? null);
        $studios = $this->joinNames($data['studios'] ?? null);

        $year = $data['year'] ?? null;
        $year = is_numeric($year) ? (int) $year : null;
        if (! $year) {
            $aired = $data['aired'] ?? null;
            $from = is_array($aired) ? ($aired['from'] ?? null) : null;
            if (is_string($from) && $from !== '') {
                $year = (int) substr($from, 0, 4);
            }
        }

        $images = $data['images'] ?? null;
        $jpg = is_array($images) ? ($images['jpg'] ?? null) : null;
        $poster = null;
        if (is_array($jpg)) {
            $poster = is_string($jpg['large_image_url'] ?? null)
                ? $jpg['large_image_url']
                : (is_string($jpg['image_url'] ?? null) ? $jpg['image_url'] : null);
        }

        return [
            'title' => $data['title'] ?? null,
            'original_title' => $data['title_japanese'] ?? null,
            'description' => $data['synopsis'] ?? null,
            'poster_url' => $poster,
            'year' => $year ?: null,
            'episodes_total' => $data['episodes'] ?? null,
            'runtime_minutes' => $this->parseDuration(is_string($data['duration'] ?? null) ? $data['duration'] : null),
            'genres' => $genres ?: null,
            'studios' => $studios ?: null,
            'media_type' => $this->normalizeMediaType(is_string($data['type'] ?? null) ? $data['type'] : null),
            'mal_id' => $data['mal_id'] ?? null,
            'mal_score' => $data['score'] ?? null,
            'mal_url' => $data['url'] ?? null,
        ];
    }

    /**
     * Unique, comma-joined "name" values across one or more list payloads.
     */
    protected function joinNames(mixed ...$lists): string
    {
        $names = [];
        foreach ($lists as $list) {
            if (! is_array($list)) {
                continue;
            }
            foreach ($list as $item) {
                if (is_array($item) && is_string($item['name'] ?? null)) {
                    $names[$item['name']] = true;
                }
            }
        }

        return implode(', ', array_keys($names));
    }

    protected function parseDuration(?string $duration): ?int
    {
        if (empty($duration) || $duration === 'Unknown') {
            return null;
        }

        $minutes = 0;

        if (preg_match('/(\d+)\s*hr/', $duration, $matches)) {
            $minutes += (int) $matches[1] * 60;
        }

        if (preg_match('/(\d+)\s*min/', $duration, $matches)) {
            $minutes += (int) $matches[1];
        }

        return $minutes > 0 ? $minutes : null;
    }

    protected function normalizeMediaType(?string $type): ?string
    {
        if (empty($type)) {
            return null;
        }

        return match ($type) {
            'TV' => 'TV',
            'Movie' => 'Movie',
            'OVA' => 'OVA',
            'ONA' => 'ONA',
            'Special' => 'Special',
            'Music' => 'Music',
            default => $type,
        };
    }
}
