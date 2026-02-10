<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class JikanService
{
    protected const BASE_URL = 'https://api.jikan.moe/v4';

    protected const TIMEOUT = 30;

    protected const RATE_LIMIT_DELAY_MS = 400;

    public function findByMalId(int $malId): ?array
    {
        try {
            usleep(self::RATE_LIMIT_DELAY_MS * 1000);

            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL . '/anime/' . $malId);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json('data');

            if (empty($data)) {
                return null;
            }

            return $this->normalizeData($data);
        } catch (\Exception) {
            return null;
        }
    }

    public function searchByTitle(string $title): ?array
    {
        try {
            usleep(self::RATE_LIMIT_DELAY_MS * 1000);

            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL . '/anime', [
                    'q' => $title,
                    'limit' => 1,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $results = $response->json('data');

            if (empty($results)) {
                return null;
            }

            return $this->normalizeData($results[0]);
        } catch (\Exception) {
            return null;
        }
    }

    public function normalizeData(array $data): array
    {
        $genres = collect($data['genres'] ?? [])
            ->merge($data['themes'] ?? [])
            ->pluck('name')
            ->unique()
            ->implode(', ');

        $studios = collect($data['studios'] ?? [])
            ->pluck('name')
            ->implode(', ');

        $year = $data['year'] ?? null;
        if (! $year && ! empty($data['aired']['from'])) {
            $year = (int) substr($data['aired']['from'], 0, 4);
        }

        return [
            'title' => $data['title'] ?? null,
            'original_title' => $data['title_japanese'] ?? null,
            'description' => $data['synopsis'] ?? null,
            'poster_url' => $data['images']['jpg']['large_image_url'] ?? $data['images']['jpg']['image_url'] ?? null,
            'year' => $year ?: null,
            'episodes_total' => $data['episodes'] ?? null,
            'runtime_minutes' => $this->parseDuration($data['duration'] ?? null),
            'genres' => $genres ?: null,
            'studios' => $studios ?: null,
            'media_type' => $this->normalizeMediaType($data['type'] ?? null),
            'mal_id' => $data['mal_id'] ?? null,
            'mal_score' => $data['score'] ?? null,
            'mal_url' => $data['url'] ?? null,
        ];
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
