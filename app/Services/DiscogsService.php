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
        $this->connector = new DiscogsConnector;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $query, string $type = 'master'): array
    {
        try {
            // Search by artist first, then by general query, merge with artist results prioritized
            $artistResponse = $this->connector->send(new SearchReleases($query, $type, artistMode: true));
            $generalResponse = $this->connector->send(new SearchReleases($query, $type));

            $results = [];
            $seenIds = [];

            foreach ([$artistResponse, $generalResponse] as $response) {
                if (! $response->successful()) {
                    continue;
                }

                $data = $response->json();
                $rows = is_array($data['results'] ?? null) ? $data['results'] : [];

                foreach ($rows as $result) {
                    if (! is_array($result)) {
                        continue;
                    }

                    $id = $result['id'] ?? null;
                    $seenKey = is_scalar($id) ? (string) $id : null;
                    if ($seenKey === null || isset($seenIds[$seenKey])) {
                        continue;
                    }
                    $seenIds[$seenKey] = true;

                    $format = $result['format'] ?? null;
                    $label = $result['label'] ?? null;

                    $results[] = [
                        'id' => $id,
                        'master_id' => $result['master_id'] ?? $id,
                        'title' => $result['title'] ?? 'Unknown',
                        'year' => $result['year'] ?? null,
                        'cover_url' => $this->bestSearchImage($result),
                        'genre' => $result['genre'] ?? [],
                        'style' => $result['style'] ?? [],
                        'format' => is_array($format) ? implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : '', $format)) : null,
                        'label' => is_array($label) ? ($label[0] ?? null) : null,
                        'country' => $result['country'] ?? null,
                        'type' => $result['type'] ?? 'master',
                    ];
                }
            }

            return array_slice($results, 0, 30);
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
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

    /**
     * @return array<string, mixed>|null
     */
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

    /**
     * @param  array<array-key, mixed>  $result
     */
    protected function bestSearchImage(array $result): ?string
    {
        // cover_image is often a spacer GIF; prefer thumb which has real thumbnails
        $cover = $result['cover_image'] ?? null;
        $thumb = $result['thumb'] ?? null;

        if (is_string($cover) && $cover !== '' && ! str_contains($cover, 'spacer')) {
            return $cover;
        }

        if (is_string($thumb) && $thumb !== '') {
            return $thumb;
        }

        return is_string($cover) && $cover !== '' ? $cover : null;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeRelease(array $data, string $type): array
    {
        $artists = [];
        foreach (is_array($data['artists'] ?? null) ? $data['artists'] : [] as $a) {
            $artists[] = is_array($a) && is_string($a['name'] ?? null) ? $a['name'] : 'Unknown';
        }
        $artistName = $artists !== [] ? implode(', ', $artists) : 'Unknown';
        // Clean Discogs numbered suffixes like "Artist (2)"
        $artistName = preg_replace('/\s*\(\d+\)/', '', $artistName) ?? $artistName;

        $tracklist = [];
        foreach (is_array($data['tracklist'] ?? null) ? $data['tracklist'] : [] as $t) {
            if (! is_array($t)) {
                continue;
            }
            $tracklist[] = [
                'position' => $t['position'] ?? '',
                'title' => $t['title'] ?? 'Unknown',
                'duration' => $t['duration'] ?? '',
            ];
        }

        $coverUrl = null;
        foreach (is_array($data['images'] ?? null) ? $data['images'] : [] as $image) {
            if (is_array($image) && ($image['type'] ?? '') === 'primary') {
                $coverUrl = is_string($image['uri'] ?? null) ? $image['uri'] : null;
                break;
            }
        }
        if ($coverUrl === null && is_array($data['images'] ?? null) && is_array($data['images'][0] ?? null)) {
            $coverUrl = is_string($data['images'][0]['uri'] ?? null) ? $data['images'][0]['uri'] : null;
        }

        $formats = [];
        foreach (is_array($data['formats'] ?? null) ? $data['formats'] : [] as $f) {
            if (is_array($f)) {
                $formats[] = is_string($f['name'] ?? null) ? $f['name'] : '';
            }
        }

        $title = $data['title'] ?? 'Unknown';
        $title = is_string($title) ? (preg_replace('/\s*\(\d+\)/', '', $title) ?? $title) : 'Unknown';

        $labels = $data['labels'] ?? null;
        $label = null;
        if (is_array($labels) && is_array($labels[0] ?? null)) {
            $label = is_string($labels[0]['name'] ?? null) ? $labels[0]['name'] : null;
        }

        return [
            'title' => $title,
            'artist' => $artistName,
            'genre' => $data['genres'] ?? [],
            'styles' => $data['styles'] ?? [],
            'year' => $data['year'] ?? null,
            'format' => $formats !== [] ? implode(', ', $formats) : null,
            'label' => $label,
            'country' => $data['country'] ?? null,
            'cover_url' => $coverUrl,
            'tracklist' => $tracklist,
            'discogs_id' => $data['id'] ?? null,
            'discogs_master_id' => $type === 'master' ? ($data['id'] ?? null) : ($data['master_id'] ?? null),
        ];
    }
}
