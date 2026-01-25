<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class OpenLibraryService
{
    protected const BASE_URL = 'https://openlibrary.org';

    protected const TIMEOUT = 30;

    public function fetchByIsbn(string $isbn): ?array
    {
        $isbn = preg_replace('/[^0-9X]/i', '', $isbn);

        if (empty($isbn)) {
            return null;
        }

        try {
            // Fetch edition data (page count, publisher, etc.)
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL."/isbn/{$isbn}.json");

            if (! $response->successful()) {
                return null;
            }

            $editionData = $response->json();

            // Try to fetch work data for description (descriptions are stored at work level)
            $workData = $this->fetchWorkData($editionData);

            return $this->normalizeData($editionData, $workData);
        } catch (\Exception) {
            return null;
        }
    }

    protected function fetchWorkData(array $editionData): ?array
    {
        $works = $editionData['works'] ?? [];

        if (empty($works) || !isset($works[0]['key'])) {
            return null;
        }

        try {
            $workKey = $works[0]['key']; // e.g., "/works/OL123W"
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::BASE_URL.$workKey.'.json');

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception) {
            // Silently fail - work data is optional
        }

        return null;
    }

    protected function normalizeData(array $editionData, ?array $workData = null): array
    {
        // Try to get description from edition first, then fall back to work
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
            // Try various date formats OpenLibrary uses
            // Full date: "April 10, 1925", "1925-04-10"
            // Year only: "1925"
            // Month/Year: "April 1925"

            // If just a year
            if (preg_match('/^\d{4}$/', $date)) {
                return $date.'-01-01';
            }

            // Try to parse with Carbon
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception) {
            // If parsing fails, try to extract just the year
            if (preg_match('/(\d{4})/', $date, $matches)) {
                return $matches[1].'-01-01';
            }

            return null;
        }
    }
}
