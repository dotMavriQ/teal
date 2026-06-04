<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\ComicVine\ComicVineConnector;
use App\Services\Saloon\ComicVine\Requests\GetIssues;
use App\Services\Saloon\ComicVine\Requests\GetVolumeDetails;
use App\Services\Saloon\ComicVine\Requests\SearchVolumes;
use Exception;
use Illuminate\Support\Facades\Log;

class ComicVineService
{
    protected ComicVineConnector $connector;

    public function __construct()
    {
        $this->connector = new ComicVineConnector;
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.comic_vine.api_key'));
    }

    /**
     * @return list<array<string, mixed>>
     */
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

            $volumes = [];
            foreach (is_array($data['results'] ?? null) ? $data['results'] : [] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (($item['resource_type'] ?? '') !== 'volume') {
                    continue;
                }
                $volumes[] = $this->mapVolume($item);
            }

            return $volumes;
        } catch (Exception $e) {
            Log::warning('ComicVine API error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
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

            $result = $data['results'] ?? null;
            if (! is_array($result)) {
                return null;
            }

            $volume = $this->mapVolume($result);
            $volume['creators'] = $this->namesList($result['people'] ?? null) ?: null;
            $volume['characters'] = $this->namesList($result['characters'] ?? null) ?: null;

            return $volume;
        } catch (Exception $e) {
            Log::warning('ComicVine API error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchVolumeIssues(string $volumeId): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $allIssues = [];
        $offset = 0;
        $limit = 100;
        $maxPages = 10;
        $page = 0;

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

                $results = is_array($data['results'] ?? null) ? $data['results'] : [];
                $totalResults = is_int($data['number_of_total_results'] ?? null) ? $data['number_of_total_results'] : 0;

                foreach ($results as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $desc = $item['description'] ?? null;
                    $allIssues[] = [
                        'issue_id' => $this->str($item['id'] ?? null),
                        'title' => is_string($item['name'] ?? null) ? $item['name'] : null,
                        'issue_number' => $item['issue_number'] ?? null,
                        'cover_date' => $item['cover_date'] ?? null,
                        'cover_url' => $this->imageUrl($item['image'] ?? null),
                        'description' => $this->stripHtml(is_string($desc) ? $desc : null),
                        'comicvine_url' => is_string($item['site_detail_url'] ?? null) ? $item['site_detail_url'] : null,
                    ];
                }

                $offset += $limit;
                $page++;

                // Rate limiting is handled by the connector via Saloon's rate limit plugin
            } while ($offset < $totalResults && $results !== [] && $page < $maxPages);
        } catch (Exception $e) {
            Log::warning('ComicVine API error during issue fetch: '.$e->getMessage());
        }

        return $allIssues;
    }

    /**
     * Map a ComicVine volume payload to our normalized shape.
     *
     * @param  array<array-key, mixed>  $item
     * @return array<string, mixed>
     */
    protected function mapVolume(array $item): array
    {
        $publisher = $item['publisher'] ?? null;
        $startYear = $item['start_year'] ?? null;
        $desc = $item['description'] ?? null;

        return [
            'volume_id' => $this->str($item['id'] ?? null),
            'title' => is_string($item['name'] ?? null) ? $item['name'] : '',
            'publisher' => is_array($publisher) && is_string($publisher['name'] ?? null) ? $publisher['name'] : null,
            'start_year' => is_numeric($startYear) ? (int) $startYear : null,
            'issue_count' => $item['count_of_issues'] ?? null,
            'cover_url' => $this->imageUrl($item['image'] ?? null),
            'description' => $this->stripHtml(is_string($desc) ? $desc : null),
            'comicvine_url' => is_string($item['site_detail_url'] ?? null) ? $item['site_detail_url'] : null,
        ];
    }

    /**
     * Prefer the medium image, fall back to small.
     */
    protected function imageUrl(mixed $image): ?string
    {
        if (! is_array($image)) {
            return null;
        }

        $medium = $image['medium_url'] ?? null;
        if (is_string($medium) && $medium !== '') {
            return $medium;
        }

        $small = $image['small_url'] ?? null;

        return is_string($small) && $small !== '' ? $small : null;
    }

    /**
     * Comma-joined list of up to 20 "name" values from a list payload.
     */
    protected function namesList(mixed $items): string
    {
        if (! is_array($items)) {
            return '';
        }

        $names = [];
        foreach ($items as $i) {
            if (is_array($i) && is_string($i['name'] ?? null)) {
                $names[] = $i['name'];
            }
            if (count($names) >= 20) {
                break;
            }
        }

        return implode(', ', $names);
    }

    protected function str(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    protected function stripHtml(?string $html): ?string
    {
        if (empty($html)) {
            return null;
        }

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text) ?: null;
    }
}
