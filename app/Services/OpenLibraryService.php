<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\OpenLibrary\OpenLibraryConnector;
use App\Services\Saloon\OpenLibrary\Requests\GetIsbnDetails;
use App\Services\Saloon\OpenLibrary\Requests\GetWorkDetails;
use App\Services\Saloon\OpenLibrary\Requests\SearchBooks;
use Carbon\Carbon;
use Exception;

class OpenLibraryService
{
    protected OpenLibraryConnector $connector;

    public function __construct()
    {
        $this->connector = new OpenLibraryConnector;
    }

    /**
     * @return array{results: list<array<string, mixed>>, total: int, total_pages: int}
     */
    public function search(string $query, int $page = 1): array
    {
        try {
            $response = $this->connector->send(new SearchBooks($query, $page));

            if (! $response->successful()) {
                return ['results' => [], 'total' => 0, 'total_pages' => 0];
            }

            $data = $response->json();
            $docs = is_array($data['docs'] ?? null) ? $data['docs'] : [];
            $numFound = is_int($data['numFound'] ?? null) ? $data['numFound'] : 0;

            $results = [];
            foreach ($docs as $doc) {
                if (! is_array($doc)) {
                    continue;
                }

                $coverId = $doc['cover_i'] ?? null;
                $coverId = is_int($coverId) || is_string($coverId) ? $coverId : null;
                $authors = is_array($doc['author_name'] ?? null) ? $doc['author_name'] : [];
                $isbns = is_array($doc['isbn'] ?? null) ? $doc['isbn'] : [];
                $publishers = is_array($doc['publisher'] ?? null) ? $doc['publisher'] : [];

                $results[] = [
                    'key' => $doc['key'] ?? '',
                    'title' => $doc['title'] ?? 'Unknown Title',
                    'author' => $authors[0] ?? null,
                    'authors' => $authors,
                    'first_publish_year' => $doc['first_publish_year'] ?? null,
                    'cover_url' => $coverId !== null
                        ? "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg"
                        : null,
                    'cover_url_large' => $coverId !== null
                        ? "https://covers.openlibrary.org/b/id/{$coverId}-L.jpg"
                        : null,
                    'isbn' => $isbns[0] ?? null,
                    'page_count' => $doc['number_of_pages_median'] ?? null,
                    'publisher' => $publishers[0] ?? null,
                    'edition_count' => $doc['edition_count'] ?? 0,
                ];
            }

            return [
                'results' => $results,
                'total' => $numFound,
                'total_pages' => (int) ceil($numFound / 20),
            ];
        } catch (Exception) {
            return ['results' => [], 'total' => 0, 'total_pages' => 0];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchByIsbn(string $isbn): ?array
    {
        $isbn = preg_replace('/[^0-9X]/i', '', $isbn) ?? '';

        if ($isbn === '') {
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
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @param  array<array-key, mixed>  $editionData
     * @return array<array-key, mixed>|null
     */
    protected function fetchWorkData(array $editionData): ?array
    {
        $works = $editionData['works'] ?? null;
        $first = is_array($works) ? ($works[0] ?? null) : null;
        $workKey = is_array($first) ? ($first['key'] ?? null) : null;

        if (! is_string($workKey)) {
            return null;
        }

        try {
            $response = $this->connector->send(new GetWorkDetails($workKey));

            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception) {
            // Work data is optional
        }

        return null;
    }

    /**
     * @param  array<array-key, mixed>  $editionData
     * @param  array<array-key, mixed>|null  $workData
     * @return array<string, mixed>
     */
    protected function normalizeData(array $editionData, ?array $workData = null): array
    {
        $description = $this->extractDescription($editionData);
        if (empty($description) && is_array($workData)) {
            $description = $this->extractDescription($workData);
        }

        return [
            'description' => $description,
            'publisher' => $this->extractPublisher($editionData),
            'page_count' => $editionData['number_of_pages'] ?? null,
            'published_date' => $this->parsePublishDate(is_string($editionData['publish_date'] ?? null) ? $editionData['publish_date'] : null),
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    protected function extractDescription(array $data): ?string
    {
        $description = $data['description'] ?? null;

        if (is_array($description)) {
            return is_string($description['value'] ?? null) ? $description['value'] : null;
        }

        return is_string($description) ? $description : null;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    protected function extractPublisher(array $data): ?string
    {
        $publishers = $data['publishers'] ?? null;

        if (is_array($publishers)) {
            return is_string($publishers[0] ?? null) ? $publishers[0] : null;
        }

        return is_string($publishers) ? $publishers : null;
    }

    protected function parsePublishDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            if (preg_match('/^\d{4}$/', $date)) {
                return $date.'-01-01';
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (Exception) {
            if (preg_match('/(\d{4})/', $date, $matches)) {
                return $matches[1].'-01-01';
            }

            return null;
        }
    }
}
