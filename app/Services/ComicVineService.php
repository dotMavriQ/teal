<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\ComicVine\ComicVineConnector;
use App\Services\Saloon\ComicVine\Requests\GetIssues;
use App\Services\Saloon\ComicVine\Requests\GetVolumeDetails;
use App\Services\Saloon\ComicVine\Requests\SearchVolumes;

class ComicVineService
{
    protected ComicVineConnector $connector;

    public function __construct()
    {
        $this->connector = new ComicVineConnector();
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.comic_vine.api_key'));
    }

    public function searchVolumes(string $query): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $response = $this->connector->send(new SearchVolumes($query));

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            if (($data['status_code'] ?? 0) !== 1) {
                return [];
            }

            return collect($data['results'] ?? [])
                ->filter(fn ($item) => ($item['resource_type'] ?? '') === 'volume')
                ->map(fn ($item) => [
                    'volume_id' => (string) $item['id'],
                    'title' => $item['name'] ?? '',
                    'publisher' => $item['publisher']['name'] ?? null,
                    'start_year' => ! empty($item['start_year']) ? (int) $item['start_year'] : null,
                    'issue_count' => $item['count_of_issues'] ?? null,
                    'cover_url' => $item['image']['medium_url'] ?? $item['image']['small_url'] ?? null,
                    'description' => $this->stripHtml($item['description'] ?? null),
                    'comicvine_url' => $item['site_detail_url'] ?? null,
                ])
                ->values()
                ->all();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('ComicVine API error: ' . $e->getMessage());

            return [];
        }
    }

    public function fetchVolumeDetails(string $volumeId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->connector->send(new GetVolumeDetails($volumeId));

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            if (($data['status_code'] ?? 0) !== 1) {
                return null;
            }

            $result = $data['results'] ?? [];

            $creators = collect($result['people'] ?? [])
                ->pluck('name')
                ->take(20)
                ->implode(', ');

            $characters = collect($result['characters'] ?? [])
                ->pluck('name')
                ->take(20)
                ->implode(', ');

            return [
                'volume_id' => (string) $result['id'],
                'title' => $result['name'] ?? '',
                'publisher' => $result['publisher']['name'] ?? null,
                'start_year' => ! empty($result['start_year']) ? (int) $result['start_year'] : null,
                'issue_count' => $result['count_of_issues'] ?? null,
                'cover_url' => $result['image']['medium_url'] ?? $result['image']['small_url'] ?? null,
                'description' => $this->stripHtml($result['description'] ?? null),
                'comicvine_url' => $result['site_detail_url'] ?? null,
                'creators' => $creators ?: null,
                'characters' => $characters ?: null,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('ComicVine API error: ' . $e->getMessage());

            return null;
        }
    }

    public function fetchVolumeIssues(string $volumeId): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $allIssues = [];
        $offset = 0;
        $limit = 100;

        try {
            do {
                $response = $this->connector->send(new GetIssues($volumeId, $offset, $limit));

                if (! $response->successful()) {
                    break;
                }

                $data = $response->json();

                if (($data['status_code'] ?? 0) !== 1) {
                    break;
                }

                $results = $data['results'] ?? [];
                $totalResults = $data['number_of_total_results'] ?? 0;

                foreach ($results as $item) {
                    $allIssues[] = [
                        'issue_id' => (string) $item['id'],
                        'title' => $item['name'] ?? null,
                        'issue_number' => $item['issue_number'] ?? null,
                        'cover_date' => $item['cover_date'] ?? null,
                        'cover_url' => $item['image']['medium_url'] ?? $item['image']['small_url'] ?? null,
                        'description' => $this->stripHtml($item['description'] ?? null),
                        'comicvine_url' => $item['site_detail_url'] ?? null,
                    ];
                }

                $offset += $limit;

                // Respect rate limits if we need more pages
                if ($offset < $totalResults && ! empty($results)) {
                    usleep(400000);
                }
            } while ($offset < $totalResults && ! empty($results));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('ComicVine API error during issue fetch: ' . $e->getMessage());
        }

        return $allIssues;
    }

    protected function stripHtml(?string $html): ?string
    {
        if (empty($html)) {
            return null;
        }

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text) ?: null;
    }
}
