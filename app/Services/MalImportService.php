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
    /**
     * @return Collection<int, array<string, mixed>>
     */
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

        return collect($data)
            ->map(fn ($entry) => $this->mapJsonEntryToAnime(is_array($entry) ? $entry : []))
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function parseXml(string $content): Collection
    {
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new \InvalidArgumentException('Invalid XML format. Could not parse the MAL export file.');
        }

        /** @var Collection<int, array<string, mixed>> $entries */
        $entries = collect();

        foreach ($xml->anime as $anime) {
            $entries->push($this->mapXmlEntryToAnime($anime));
        }

        return $entries;
    }

    /**
     * @param  array<array-key, mixed>  $entry
     * @return array<string, mixed>
     */
    public function mapJsonEntryToAnime(array $entry): array
    {
        $malStatus = $this->toInt($entry['status'] ?? null);
        $score = $this->toInt($entry['score'] ?? null);

        $genres = $this->joinNames($entry['genres'] ?? null);

        $episodesTotal = $this->toInt($entry['anime_num_episodes'] ?? null);
        $episodesWatched = $this->toInt($entry['num_watched_episodes'] ?? null);
        $malId = $this->toInt($entry['anime_id'] ?? null);
        $malScore = $this->toFloat($entry['anime_score_val'] ?? null);

        $title = $entry['anime_title'] ?? '';
        $titleEng = $entry['anime_title_eng'] ?? null;

        return [
            'title' => $title,
            'original_title' => (! empty($titleEng) && $titleEng !== $title) ? $titleEng : null,
            'poster_url' => $this->normalizeImageUrl(is_string($entry['anime_image_path'] ?? null) ? $entry['anime_image_path'] : null),
            'episodes_total' => $episodesTotal > 0 ? $episodesTotal : null,
            'episodes_watched' => $episodesWatched > 0 ? $episodesWatched : null,
            'media_type' => $this->normalizeMediaType(is_string($entry['anime_media_type_string'] ?? null) ? $entry['anime_media_type_string'] : null),
            'status' => $this->mapMalStatus($malStatus),
            'rating' => $score > 0 && $score <= 10 ? $score : null,
            'mal_id' => $malId > 0 ? $malId : null,
            'mal_score' => $malScore > 0 ? $malScore : null,
            'mal_url' => $malId > 0 ? "https://myanimelist.net/anime/{$malId}" : null,
            'genres' => $genres ?: null,
            'tags' => ! empty($entry['tags']) ? $entry['tags'] : null,
            'date_started' => $this->parseMalDateString(is_string($entry['start_date_string'] ?? null) ? $entry['start_date_string'] : null),
            'date_finished' => $this->parseMalDateString(is_string($entry['finish_date_string'] ?? null) ? $entry['finish_date_string'] : null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mapXmlEntryToAnime(\SimpleXMLElement $entry): array
    {
        $malStatus = (int) (string) $entry->my_status;
        $score = (int) (string) $entry->my_score;
        $malId = (int) (string) $entry->series_animedb_id;

        $dateStarted = $this->parseMalXmlDate((string) $entry->my_start_date);
        $dateFinished = $this->parseMalXmlDate((string) $entry->my_finish_date);

        $episodesTotal = (int) (string) $entry->series_episodes;
        $episodesWatched = (int) (string) $entry->my_watched_episodes;
        $tags = (string) $entry->my_tags;
        $comments = (string) $entry->my_comments;

        return [
            'title' => (string) $entry->series_title,
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
            'tags' => $tags !== '' ? $tags : null,
            'notes' => $comments !== '' ? $comments : null,
            'date_started' => $dateStarted,
            'date_finished' => $dateFinished,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $entries
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function importAll(User $user, Collection $entries, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Pre-load existing MAL IDs for batch duplicate detection
        $existingMalIds = [];
        if ($skipDuplicates) {
            $existingMalIds = Anime::where('user_id', $user->id)
                ->whereNotNull('mal_id')
                ->pluck('mal_id')
                ->flip()
                ->all();
        }

        foreach ($entries as $animeData) {
            try {
                if (empty($animeData['title'])) {
                    $errors[] = 'Entry with missing title skipped.';

                    continue;
                }

                $malId = $animeData['mal_id'] ?? null;
                $malKey = is_int($malId) || is_string($malId) ? $malId : null;
                if ($skipDuplicates && $malKey !== null && isset($existingMalIds[$malKey])) {
                    $skipped++;

                    continue;
                }

                $status = $animeData['status'] ?? null;
                $animeData['status'] = $status instanceof WatchingStatus ? $status->value : $status;
                $animeData['user_id'] = $user->id;
                $animeData['date_added'] = now()->format('Y-m-d');

                Anime::create($animeData);
                $imported++;

                // Update cache with newly imported anime
                if ($skipDuplicates && $malKey !== null) {
                    $existingMalIds[$malKey] = true;
                }
            } catch (\Exception $e) {
                $title = $animeData['title'];
                $errors[] = '"'.(is_scalar($title) ? (string) $title : 'Unknown').'": '.$e->getMessage();
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
            return 'https:'.$url;
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

    protected function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    protected function toFloat(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * Comma-joined "name" values from a list payload.
     */
    protected function joinNames(mixed $items): string
    {
        if (! is_array($items)) {
            return '';
        }

        $names = [];
        foreach ($items as $item) {
            if (is_array($item) && is_string($item['name'] ?? null)) {
                $names[] = $item['name'];
            }
        }

        return implode(', ', $names);
    }
}
