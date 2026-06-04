<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\SetlistFm\Requests\GetArtistSetlists;
use App\Services\Saloon\SetlistFm\Requests\SearchArtists;
use App\Services\Saloon\SetlistFm\Requests\SearchSetlists;
use App\Services\Saloon\SetlistFm\SetlistFmConnector;
use Exception;

class SetlistFmService
{
    protected SetlistFmConnector $connector;

    public function __construct()
    {
        $this->connector = new SetlistFmConnector;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchArtists(string $query): array
    {
        try {
            $response = $this->connector->send(new SearchArtists($query));

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            $artists = is_array($data['artist'] ?? null) ? $data['artist'] : [];

            $out = [];
            foreach (array_slice($artists, 0, 20) as $artist) {
                if (! is_array($artist)) {
                    continue;
                }
                $out[] = [
                    'mbid' => $artist['mbid'] ?? null,
                    'name' => $artist['name'] ?? 'Unknown',
                    'sort_name' => $artist['sortName'] ?? null,
                    'disambiguation' => $artist['disambiguation'] ?? null,
                ];
            }

            return $out;
        } catch (Exception) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getArtistSetlists(string $mbid, int $page = 1): array
    {
        try {
            $response = $this->connector->send(new GetArtistSetlists($mbid, $page));

            if (! $response->successful()) {
                return ['setlists' => [], 'total' => 0];
            }

            $data = $response->json();

            return [
                'setlists' => $this->normalizeSetlists($data['setlist'] ?? null),
                'total' => $data['total'] ?? 0,
                'page' => $data['page'] ?? 1,
                'items_per_page' => $data['itemsPerPage'] ?? 20,
            ];
        } catch (Exception) {
            return ['setlists' => [], 'total' => 0];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchSetlists(string $artistName, ?string $cityName = null, ?string $year = null): array
    {
        try {
            $response = $this->connector->send(new SearchSetlists($artistName, $cityName, $year));

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            return $this->normalizeSetlists($data['setlist'] ?? null);
        } catch (Exception) {
            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function normalizeSetlists(mixed $setlists): array
    {
        $out = [];
        foreach (is_array($setlists) ? $setlists : [] as $setlist) {
            if (is_array($setlist)) {
                $out[] = $this->normalizeSetlist($setlist);
            }
        }

        return $out;
    }

    /**
     * @param  array<array-key, mixed>  $setlist
     * @return array<string, mixed>
     */
    protected function normalizeSetlist(array $setlist): array
    {
        $venue = is_array($setlist['venue'] ?? null) ? $setlist['venue'] : [];
        $city = is_array($venue['city'] ?? null) ? $venue['city'] : [];
        $country = is_array($city['country'] ?? null) ? $city['country'] : [];
        $artist = is_array($setlist['artist'] ?? null) ? $setlist['artist'] : [];
        $tour = is_array($setlist['tour'] ?? null) ? $setlist['tour'] : [];

        $songs = [];
        $sets = $setlist['sets'] ?? null;
        $setArray = is_array($sets) ? ($sets['set'] ?? null) : null;
        foreach (is_array($setArray) ? $setArray : [] as $set) {
            if (! is_array($set)) {
                continue;
            }
            $setName = $set['name'] ?? null;
            $encore = $set['encore'] ?? null;
            foreach (is_array($set['song'] ?? null) ? $set['song'] : [] as $song) {
                if (! is_array($song)) {
                    continue;
                }
                $cover = $song['cover'] ?? null;
                $with = $song['with'] ?? null;
                $songs[] = [
                    'name' => $song['name'] ?? 'Unknown',
                    'set' => $setName,
                    'encore' => $encore,
                    'cover' => is_array($cover) ? ($cover['name'] ?? null) : null,
                    'with' => is_array($with) ? ($with['name'] ?? null) : null,
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
            'event_date' => $this->parseDate(is_string($setlist['eventDate'] ?? null) ? $setlist['eventDate'] : null),
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
