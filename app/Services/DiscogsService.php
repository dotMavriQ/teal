<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\Discogs\DiscogsConnector;
use App\Services\Saloon\Discogs\Requests\GetMasterRelease;
use App\Services\Saloon\Discogs\Requests\GetRelease;
use App\Services\Saloon\Discogs\Requests\SearchReleases;

class DiscogsService
{
    protected DiscogsConnector $connector;

    public function __construct()
    {
        $this->connector = new DiscogsConnector();
    }

    public function search(string $query, string $type = 'master'): array
    {
        try {
            $response = $this->connector->send(new SearchReleases($query, $type));

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            return array_map(fn (array $result) => [
                'id' => $result['id'] ?? null,
                'master_id' => $result['master_id'] ?? $result['id'] ?? null,
                'title' => $result['title'] ?? 'Unknown',
                'year' => $result['year'] ?? null,
                'cover_url' => $result['cover_image'] ?? $result['thumb'] ?? null,
                'genre' => $result['genre'] ?? [],
                'style' => $result['style'] ?? [],
                'format' => isset($result['format']) ? implode(', ', $result['format']) : null,
                'label' => isset($result['label']) ? $result['label'][0] ?? null : null,
                'country' => $result['country'] ?? null,
                'type' => $result['type'] ?? 'master',
            ], $data['results'] ?? []);
        } catch (\Exception) {
            return [];
        }
    }

    public function getMasterDetails(int $masterId): ?array
    {
        try {
            $response = $this->connector->send(new GetMasterRelease($masterId));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            return $this->normalizeRelease($data, 'master');
        } catch (\Exception) {
            return null;
        }
    }

    public function getReleaseDetails(int $releaseId): ?array
    {
        try {
            $response = $this->connector->send(new GetRelease($releaseId));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            return $this->normalizeRelease($data, 'release');
        } catch (\Exception) {
            return null;
        }
    }

    protected function normalizeRelease(array $data, string $type): array
    {
        $artists = array_map(
            fn (array $a) => $a['name'] ?? 'Unknown',
            $data['artists'] ?? []
        );
        $artistName = implode(', ', $artists) ?: 'Unknown';
        // Clean Discogs numbered suffixes like "Artist (2)"
        $artistName = preg_replace('/\s*\(\d+\)/', '', $artistName);

        $tracklist = array_map(fn (array $t) => [
            'position' => $t['position'] ?? '',
            'title' => $t['title'] ?? 'Unknown',
            'duration' => $t['duration'] ?? '',
        ], $data['tracklist'] ?? []);

        $coverUrl = null;
        foreach ($data['images'] ?? [] as $image) {
            if (($image['type'] ?? '') === 'primary') {
                $coverUrl = $image['uri'] ?? null;
                break;
            }
        }
        if (! $coverUrl && ! empty($data['images'])) {
            $coverUrl = $data['images'][0]['uri'] ?? null;
        }

        $formats = [];
        foreach ($data['formats'] ?? [] as $f) {
            $formats[] = $f['name'] ?? '';
        }

        return [
            'title' => preg_replace('/\s*\(\d+\)/', '', $data['title'] ?? 'Unknown'),
            'artist' => $artistName,
            'genre' => $data['genres'] ?? [],
            'styles' => $data['styles'] ?? [],
            'year' => $data['year'] ?? null,
            'format' => ! empty($formats) ? implode(', ', $formats) : null,
            'label' => isset($data['labels']) ? ($data['labels'][0]['name'] ?? null) : null,
            'country' => $data['country'] ?? null,
            'cover_url' => $coverUrl,
            'tracklist' => $tracklist,
            'discogs_id' => $data['id'] ?? null,
            'discogs_master_id' => $type === 'master' ? ($data['id'] ?? null) : ($data['master_id'] ?? null),
        ];
    }
}
