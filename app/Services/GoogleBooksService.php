<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\GoogleBooks\GoogleBooksConnector;
use App\Services\Saloon\GoogleBooks\Requests\SearchVolumes;

class GoogleBooksService
{
    protected GoogleBooksConnector $connector;

    public function __construct()
    {
        $this->connector = new GoogleBooksConnector;
    }

    /**
     * @return array{results: list<array<string, mixed>>, total: int, total_pages: int}
     */
    public function search(string $query, int $page = 1): array
    {
        try {
            $startIndex = ($page - 1) * 20;
            $response = $this->connector->send(new SearchVolumes($query, $startIndex));

            if (! $response->successful()) {
                return ['results' => [], 'total' => 0, 'total_pages' => 0];
            }

            $data = $response->json();
            $totalItems = is_int($data['totalItems'] ?? null) ? $data['totalItems'] : 0;
            $items = is_array($data['items'] ?? null) ? $data['items'] : [];

            $results = [];
            foreach ($items as $item) {
                if (is_array($item)) {
                    $results[] = $this->mapVolume($item);
                }
            }

            return [
                'results' => $results,
                'total' => $totalItems,
                'total_pages' => (int) ceil($totalItems / 20),
            ];
        } catch (\Exception) {
            return ['results' => [], 'total' => 0, 'total_pages' => 0];
        }
    }

    /**
     * @param  array<array-key, mixed>  $item
     * @return array<string, mixed>
     */
    protected function mapVolume(array $item): array
    {
        $info = is_array($item['volumeInfo'] ?? null) ? $item['volumeInfo'] : [];
        $identifiers = is_array($info['industryIdentifiers'] ?? null) ? $info['industryIdentifiers'] : [];

        $isbn13 = null;
        $isbn10 = null;
        foreach ($identifiers as $id) {
            if (! is_array($id)) {
                continue;
            }
            $type = $id['type'] ?? null;
            $identifier = $id['identifier'] ?? null;
            if ($type === 'ISBN_13') {
                $isbn13 = $identifier;
            } elseif ($type === 'ISBN_10') {
                $isbn10 = $identifier;
            }
        }

        $imageLinks = is_array($info['imageLinks'] ?? null) ? $info['imageLinks'] : [];
        $coverUrl = is_string($imageLinks['thumbnail'] ?? null) ? $imageLinks['thumbnail'] : null;
        $coverUrlLarge = null;
        foreach (['large', 'medium', 'small', 'thumbnail'] as $size) {
            if (is_string($imageLinks[$size] ?? null)) {
                $coverUrlLarge = $imageLinks[$size];
                break;
            }
        }

        // Google Books serves http URLs — upgrade to https
        if ($coverUrl !== null) {
            $coverUrl = str_replace('http://', 'https://', $coverUrl);
        }
        if ($coverUrlLarge !== null) {
            $coverUrlLarge = str_replace('http://', 'https://', $coverUrlLarge);
        }

        $authors = is_array($info['authors'] ?? null) ? $info['authors'] : [];
        $publishedDate = $info['publishedDate'] ?? null;
        $firstYear = is_string($publishedDate) && $publishedDate !== ''
            ? (int) substr($publishedDate, 0, 4)
            : null;

        return [
            'key' => $item['id'] ?? '',
            'title' => $info['title'] ?? 'Unknown Title',
            'subtitle' => $info['subtitle'] ?? null,
            'author' => $authors[0] ?? null,
            'authors' => $authors,
            'first_publish_year' => $firstYear,
            'published_date' => $publishedDate,
            'cover_url' => $coverUrl,
            'cover_url_large' => $coverUrlLarge,
            'isbn' => $isbn13 ?? $isbn10,
            'page_count' => $info['pageCount'] ?? null,
            'publisher' => $info['publisher'] ?? null,
            'description' => $info['description'] ?? null,
            'edition_count' => 0,
            'source' => 'google_books',
        ];
    }
}
