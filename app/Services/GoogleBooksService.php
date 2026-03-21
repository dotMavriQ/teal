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
        $this->connector = new GoogleBooksConnector();
    }

    public function search(string $query, int $page = 1): array
    {
        try {
            $startIndex = ($page - 1) * 20;
            $response = $this->connector->send(new SearchVolumes($query, $startIndex));

            if (! $response->successful()) {
                return ['results' => [], 'total' => 0, 'total_pages' => 0];
            }

            $data = $response->json();
            $totalItems = $data['totalItems'] ?? 0;
            $items = $data['items'] ?? [];

            $results = array_map(function (array $item) {
                $info = $item['volumeInfo'] ?? [];
                $identifiers = $info['industryIdentifiers'] ?? [];

                $isbn13 = null;
                $isbn10 = null;
                foreach ($identifiers as $id) {
                    if ($id['type'] === 'ISBN_13') {
                        $isbn13 = $id['identifier'];
                    } elseif ($id['type'] === 'ISBN_10') {
                        $isbn10 = $id['identifier'];
                    }
                }

                $coverUrl = $info['imageLinks']['thumbnail'] ?? null;
                $coverUrlLarge = $info['imageLinks']['large']
                    ?? $info['imageLinks']['medium']
                    ?? $info['imageLinks']['small']
                    ?? $info['imageLinks']['thumbnail']
                    ?? null;

                // Google Books serves http URLs — upgrade to https
                if ($coverUrl) {
                    $coverUrl = str_replace('http://', 'https://', $coverUrl);
                }
                if ($coverUrlLarge) {
                    $coverUrlLarge = str_replace('http://', 'https://', $coverUrlLarge);
                }

                return [
                    'key' => $item['id'] ?? '',
                    'title' => $info['title'] ?? 'Unknown Title',
                    'subtitle' => $info['subtitle'] ?? null,
                    'author' => $info['authors'][0] ?? null,
                    'authors' => $info['authors'] ?? [],
                    'first_publish_year' => isset($info['publishedDate'])
                        ? (int) substr($info['publishedDate'], 0, 4)
                        : null,
                    'published_date' => $info['publishedDate'] ?? null,
                    'cover_url' => $coverUrl,
                    'cover_url_large' => $coverUrlLarge,
                    'isbn' => $isbn13 ?? $isbn10,
                    'page_count' => $info['pageCount'] ?? null,
                    'publisher' => $info['publisher'] ?? null,
                    'description' => $info['description'] ?? null,
                    'edition_count' => 0,
                    'source' => 'google_books',
                ];
            }, $items);

            return [
                'results' => $results,
                'total' => $totalItems,
                'total_pages' => (int) ceil($totalItems / 20),
            ];
        } catch (\Exception) {
            return ['results' => [], 'total' => 0, 'total_pages' => 0];
        }
    }
}
