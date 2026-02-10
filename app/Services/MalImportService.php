<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WatchingStatus;
use App\Models\Anime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MalImportService
{
    public function fetchFromMal(string $username): Collection
    {
        $response = Http::timeout(30)
            ->get("https://myanimelist.net/animelist/{$username}/load.json", [
                'status' => 7,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to fetch anime list for '{$username}'. MAL returned status {$response->status()}.");
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new \RuntimeException("Unexpected response format from MAL for '{$username}'.");
        }

        return collect($data)->map(fn ($entry) => $this->mapJsonEntryToAnime($entry));
    }

    public function parseXml(string $content): Collection
    {
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new \InvalidArgumentException('Invalid XML format. Could not parse the MAL export file.');
        }

        $entries = collect();

        foreach ($xml->anime as $anime) {
            $entries->push($this->mapXmlEntryToAnime($anime));
        }

        return $entries;
    }

    public function mapJsonEntryToAnime(array $entry): array
    {
        $malStatus = (int) ($entry['status'] ?? 0);
        $score = (int) ($entry['score'] ?? 0);

        $genres = '';
        if (! empty($entry['genres']) && is_array($entry['genres'])) {
            $genres = collect($entry['genres'])->pluck('name')->implode(', ');
        }

        $dateStarted = $this->parseMalDateString($entry['start_date_string'] ?? null);
        $dateFinished = $this->parseMalDateString($entry['finish_date_string'] ?? null);

        return [
            'title' => $entry['anime_title'] ?? '',
            'original_title' => ! empty($entry['anime_title_eng']) && $entry['anime_title_eng'] !== ($entry['anime_title'] ?? '') ? $entry['anime_title_eng'] : null,
            'poster_url' => $this->normalizeImageUrl($entry['anime_image_path'] ?? null),
            'episodes_total' => ($entry['anime_num_episodes'] ?? 0) > 0 ? (int) $entry['anime_num_episodes'] : null,
            'episodes_watched' => ($entry['num_watched_episodes'] ?? 0) > 0 ? (int) $entry['num_watched_episodes'] : null,
            'media_type' => $this->normalizeMediaType($entry['anime_media_type_string'] ?? null),
            'status' => $this->mapMalStatus($malStatus),
            'rating' => $score > 0 && $score <= 10 ? $score : null,
            'mal_id' => ! empty($entry['anime_id']) ? (int) $entry['anime_id'] : null,
            'mal_score' => ! empty($entry['anime_score_val']) && (float) $entry['anime_score_val'] > 0 ? (float) $entry['anime_score_val'] : null,
            'mal_url' => ! empty($entry['anime_id']) ? "https://myanimelist.net/anime/{$entry['anime_id']}" : null,
            'genres' => $genres ?: null,
            'tags' => ! empty($entry['tags']) ? $entry['tags'] : null,
            'date_started' => $dateStarted,
            'date_finished' => $dateFinished,
        ];
    }

    public function mapXmlEntryToAnime(\SimpleXMLElement $entry): array
    {
        $malStatus = (int) ($entry->my_status ?? 0);
        $score = (int) ($entry->my_score ?? 0);
        $malId = (int) ($entry->series_animedb_id ?? 0);

        $dateStarted = $this->parseMalXmlDate((string) ($entry->my_start_date ?? ''));
        $dateFinished = $this->parseMalXmlDate((string) ($entry->my_finish_date ?? ''));

        $episodesTotal = (int) ($entry->series_episodes ?? 0);
        $episodesWatched = (int) ($entry->my_watched_episodes ?? 0);

        return [
            'title' => (string) ($entry->series_title ?? ''),
            'original_title' => null,
            'poster_url' => null,
            'episodes_total' => $episodesTotal > 0 ? $episodesTotal : null,
            'episodes_watched' => $episodesWatched > 0 ? $episodesWatched : null,
            'media_type' => null,
            'status' => $this->mapMalStatus($malStatus),
            'rating' => $score > 0 && $score <= 10 ? $score : null,
            'mal_id' => $malId > 0 ? $malId : null,
            'mal_score' => null,
            'mal_url' => $malId > 0 ? "https://myanimelist.net/anime/{$malId}" : null,
            'genres' => null,
            'tags' => ! empty((string) $entry->my_tags) ? (string) $entry->my_tags : null,
            'notes' => ! empty((string) $entry->my_comments) ? (string) $entry->my_comments : null,
            'date_started' => $dateStarted,
            'date_finished' => $dateFinished,
        ];
    }

    public function importAll(User $user, Collection $entries, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($entries as $animeData) {
            try {
                if (empty($animeData['title'])) {
                    $errors[] = 'Entry with missing title skipped.';

                    continue;
                }

                if ($skipDuplicates && ! empty($animeData['mal_id'])) {
                    $existing = Anime::where('user_id', $user->id)
                        ->where('mal_id', $animeData['mal_id'])
                        ->first();

                    if ($existing) {
                        $skipped++;

                        continue;
                    }
                }

                $animeData['user_id'] = $user->id;
                $animeData['status'] = $animeData['status']->value;
                $animeData['date_added'] = now()->format('Y-m-d');

                Anime::create($animeData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = '"' . ($animeData['title'] ?? 'Unknown') . '": ' . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    protected function mapMalStatus(int $status): WatchingStatus
    {
        return match ($status) {
            1 => WatchingStatus::Watching,
            2 => WatchingStatus::Watched,
            3, 4, 6 => WatchingStatus::Watchlist,
            default => WatchingStatus::Watchlist,
        };
    }

    protected function normalizeMediaType(?string $type): ?string
    {
        if (empty($type)) {
            return null;
        }

        return match (strtolower(trim($type))) {
            'tv' => 'TV',
            'movie' => 'Movie',
            'ova' => 'OVA',
            'ona' => 'ONA',
            'special' => 'Special',
            'music' => 'Music',
            default => $type,
        };
    }

    protected function normalizeImageUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        return $url;
    }

    protected function parseMalDateString(?string $date): ?string
    {
        if (empty($date) || $date === '0000-00-00' || $date === '-') {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    protected function parseMalXmlDate(string $date): ?string
    {
        if (empty($date) || $date === '0000-00-00') {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
