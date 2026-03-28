<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\SetlistFm\Requests\GetArtistSetlists;
use App\Services\Saloon\SetlistFm\Requests\SearchArtists;
use App\Services\Saloon\SetlistFm\Requests\SearchSetlists;
use App\Services\Saloon\SetlistFm\SetlistFmConnector;

class SetlistFmService
{
    protected SetlistFmConnector $connector;

    public function __construct()
    {
        $this->connector = new SetlistFmConnector();
    }

    public function searchArtists(string $query): array
    {
        try {
            $response = $this->connector->send(new SearchArtists($query));

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            $artists = $data['artist'] ?? [];

            return array_map(fn (array $artist) => [
                'mbid' => $artist['mbid'] ?? null,
                'name' => $artist['name'] ?? 'Unknown',
                'sort_name' => $artist['sortName'] ?? null,
                'disambiguation' => $artist['disambiguation'] ?? null,
            ], array_slice($artists, 0, 20));
        } catch (\Exception) {
            return [];
        }
    }

    public function getArtistSetlists(string $mbid, int $page = 1): array
    {
        try {
            $response = $this->connector->send(new GetArtistSetlists($mbid, $page));

            if (! $response->successful()) {
                return ['setlists' => [], 'total' => 0];
            }

            $data = $response->json();

            return [
                'setlists' => array_map(
                    fn (array $setlist) => $this->normalizeSetlist($setlist),
                    $data['setlist'] ?? []
                ),
                'total' => $data['total'] ?? 0,
                'page' => $data['page'] ?? 1,
                'items_per_page' => $data['itemsPerPage'] ?? 20,
            ];
        } catch (\Exception) {
            return ['setlists' => [], 'total' => 0];
        }
    }

    public function searchSetlists(string $artistName, ?string $cityName = null, ?string $year = null): array
    {
        try {
            $response = $this->connector->send(new SearchSetlists($artistName, $cityName, $year));

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            return array_map(
                fn (array $setlist) => $this->normalizeSetlist($setlist),
                $data['setlist'] ?? []
            );
        } catch (\Exception) {
            return [];
        }
    }

    protected function normalizeSetlist(array $setlist): array
    {
        $venue = $setlist['venue'] ?? [];
        $city = $venue['city'] ?? [];
        $country = $city['country'] ?? [];
        $artist = $setlist['artist'] ?? [];
        $tour = $setlist['tour'] ?? [];

        $songs = [];
        foreach ($setlist['sets']['set'] ?? [] as $set) {
            $setName = $set['name'] ?? null;
            $encore = $set['encore'] ?? null;
            foreach ($set['song'] ?? [] as $song) {
                $songs[] = [
                    'name' => $song['name'] ?? 'Unknown',
                    'set' => $setName,
                    'encore' => $encore,
                    'cover' => isset($song['cover']) ? $song['cover']['name'] ?? null : null,
                    'with' => isset($song['with']) ? $song['with']['name'] ?? null : null,
                    'tape' => $song['tape'] ?? false,
                ];
            }
        }

        return [
            'setlist_fm_id' => $setlist['id'] ?? null,
            'artist' => $artist['name'] ?? 'Unknown',
            'artist_mbid' => $artist['mbid'] ?? null,
            'tour_name' => $tour['name'] ?? null,
            'venue' => $venue['name'] ?? null,
            'city' => $city['name'] ?? null,
            'country' => $country['name'] ?? null,
            'event_date' => $this->parseDate($setlist['eventDate'] ?? null),
            'setlist' => $songs,
            'url' => $setlist['url'] ?? null,
        ];
    }

    protected function parseDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        // Setlist.fm uses dd-MM-yyyy format
        $parts = explode('-', $date);
        if (count($parts) === 3) {
            return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
        }

        return null;
    }
}
