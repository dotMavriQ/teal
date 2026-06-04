<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Saloon\Bgg\BggConnector;
use App\Services\Saloon\Bgg\Requests\GetBoardGameDetails;
use App\Services\Saloon\Bgg\Requests\SearchBoardGames;
use Exception;
use SimpleXMLElement;

class BggService
{
    protected BggConnector $connector;

    public function __construct()
    {
        $this->connector = new BggConnector;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $query): array
    {
        try {
            // Try exact match first, then fall back to fuzzy
            $exactResponse = $this->connector->send(new SearchBoardGames($query, exact: true));
            $fuzzyResponse = $this->connector->send(new SearchBoardGames($query));

            $results = [];
            $seenIds = [];

            foreach ([$exactResponse, $fuzzyResponse] as $response) {
                if (! $response->successful()) {
                    continue;
                }

                $xml = new SimpleXMLElement($response->body());

                foreach ($xml->item as $item) {
                    $id = (int) $item['id'];
                    if (isset($seenIds[$id])) {
                        continue;
                    }
                    $seenIds[$id] = true;

                    $results[] = [
                        'bgg_id' => $id,
                        'title' => (string) $item->name['value'],
                        'year_published' => property_exists($item, 'yearpublished') && $item->yearpublished !== null ? (int) $item->yearpublished['value'] : null,
                    ];
                }
            }

            return array_slice($results, 0, 20);
        } catch (Exception) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDetails(int $bggId): ?array
    {
        try {
            $response = $this->connector->send(new GetBoardGameDetails($bggId));

            if (! $response->successful()) {
                return null;
            }

            $xml = new SimpleXMLElement($response->body());
            $item = $xml->item[0] ?? null;

            if (! $item) {
                return null;
            }

            $title = null;
            foreach ($item->name as $name) {
                if ((string) $name['type'] === 'primary') {
                    $title = (string) $name['value'];
                    break;
                }
            }

            $coverUrl = property_exists($item, 'image') && $item->image !== null ? (string) $item->image : null;

            $designers = [];
            $publishers = [];
            $genres = [];

            foreach ($item->link as $link) {
                $type = (string) $link['type'];
                $value = (string) $link['value'];

                if ($type === 'boardgamedesigner') {
                    $designers[] = $value;
                } elseif ($type === 'boardgamepublisher') {
                    $publishers[] = $value;
                } elseif ($type === 'boardgamecategory') {
                    $genres[] = $value;
                }
            }

            $bggRating = null;
            if (property_exists($item->statistics->ratings, 'average') && $item->statistics->ratings->average !== null) {
                $avg = (float) $item->statistics->ratings->average['value'];
                if ($avg > 0) {
                    $bggRating = round($avg, 2);
                }
            }

            return [
                'bgg_id' => $bggId,
                'title' => $title ?? 'Unknown',
                'description' => property_exists($item, 'description') && $item->description !== null ? html_entity_decode(strip_tags((string) $item->description)) : null,
                'cover_url' => $coverUrl,
                'year_published' => property_exists($item, 'yearpublished') && $item->yearpublished !== null ? (int) $item->yearpublished['value'] : null,
                'designer' => $designers === [] ? null : implode(', ', array_slice($designers, 0, 3)),
                'publisher' => $publishers === [] ? null : $publishers[0],
                'min_players' => property_exists($item, 'minplayers') && $item->minplayers !== null ? (int) $item->minplayers['value'] : null,
                'max_players' => property_exists($item, 'maxplayers') && $item->maxplayers !== null ? (int) $item->maxplayers['value'] : null,
                'playing_time' => property_exists($item, 'playingtime') && $item->playingtime !== null ? (int) $item->playingtime['value'] : null,
                'genres' => $genres,
                'bgg_rating' => $bggRating,
            ];
        } catch (Exception) {
            return null;
        }
    }
}
