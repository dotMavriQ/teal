<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\OpenLibrary\OpenLibraryConnector;
use App\Services\Saloon\OpenLibrary\Requests\GetIsbnDetails;
use App\Services\Saloon\OpenLibrary\Requests\GetWorkDetails;
use Carbon\Carbon;

class OpenLibraryService
{
    protected OpenLibraryConnector $connector;

    public function __construct()
    {
        $this->connector = new OpenLibraryConnector();
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
