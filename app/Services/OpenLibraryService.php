<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\OpenLibrary\OpenLibraryConnector;
use App\Services\Saloon\OpenLibrary\Requests\GetIsbnDetails;
use App\Services\Saloon\OpenLibrary\Requests\GetWorkDetails;
use App\Services\Saloon\OpenLibrary\Requests\SearchBooks;
use Carbon\Carbon;

class OpenLibraryService
{
    protected OpenLibraryConnector $connector;

    public function __construct()
    {
        $this->connector = new OpenLibraryConnector();
    }

    public function search(string $query, int $page = 1): array
    {
        try {
            $response = $this->connector->send(new SearchBooks($query, $page));

            if (! $response->successful()) {
                return ['results' => [], 'total' => 0, 'total_pages' => 0];
            }

            $data = $response->json();
            $docs = $data['docs'] ?? [];
            $numFound = $data['numFound'] ?? 0;

            $results = array_map(function (array $doc) {
                $coverId = $doc['cover_i'] ?? null;

                return [
                    'key' => $doc['key'] ?? '',
                    'title' => $doc['title'] ?? 'Unknown Title',
                    'author' => $doc['author_name'][0] ?? null,
                    'authors' => $doc['author_name'] ?? [],
                    'first_publish_year' => $doc['first_publish_year'] ?? null,
                    'cover_url' => $coverId
                        ? "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg"
                        : null,
                    'cover_url_large' => $coverId
                        ? "https://covers.openlibrary.org/b/id/{$coverId}-L.jpg"
                        : null,
                    'isbn' => $doc['isbn'][0] ?? null,
                    'page_count' => $doc['number_of_pages_median'] ?? null,
                    'publisher' => $doc['publisher'][0] ?? null,
                    'edition_count' => $doc['edition_count'] ?? 0,
                ];
            }, $docs);

            return [
                'results' => $results,
                'total' => $numFound,
                'total_pages' => (int) ceil($numFound / 20),
            ];
        } catch (\Exception) {
            return ['results' => [], 'total' => 0, 'total_pages' => 0];
        }
    }

    public function fetchByIsbn(string $isbn): ?array
    {
        $isbn = preg_replace('/[^0-9X]/i', '', $isbn);

        if (empty($isbn)) {
            return null;
        }

        try {
            // Fetch edition data
            $response = $this->connector->send(new GetIsbnDetails($isbn));

            if (! $response->successful()) {
                return null;
            }

            $editionData = $response->json();

            // Try to fetch work data for description
            $workData = $this->fetchWorkData($editionData);

            return $this->normalizeData($editionData, $workData);
        } catch (\Exception) {
            return null;
        }
    }

    protected function fetchWorkData(array $editionData): ?array
    {
        $works = $editionData['works'] ?? [];

        if (empty($works) || ! isset($works[0]['key'])) {
            return null;
        }

        try {
            $workKey = $works[0]['key'];
            $response = $this->connector->send(new GetWorkDetails($workKey));

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception) {
            // Work data is optional
        }

        return null;
    }

    protected function normalizeData(array $editionData, ?array $workData = null): array
    {
        $description = $this->extractDescription($editionData);
        if (empty($description) && $workData) {
            $description = $this->extractDescription($workData);
        }

        return [
            'description' => $description,
            'publisher' => $this->extractPublisher($editionData),
            'page_count' => $editionData['number_of_pages'] ?? null,
            'published_date' => $this->parsePublishDate($editionData['publish_date'] ?? null),
        ];
    }

    protected function extractDescription(array $data): ?string
    {
        $description = $data['description'] ?? null;

        if (is_array($description)) {
            return $description['value'] ?? null;
        }

        return $description;
    }

    protected function extractPublisher(array $data): ?string
    {
        $publishers = $data['publishers'] ?? [];

        if (empty($publishers)) {
            return null;
        }

        return is_array($publishers) ? ($publishers[0] ?? null) : $publishers;
    }

    protected function parsePublishDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            if (preg_match('/^\d{4}$/', $date)) {
                return $date . '-01-01';
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception) {
            if (preg_match('/(\d{4})/', $date, $matches)) {
                return $matches[1] . '-01-01';
            }

            return null;
        }
    }
}
