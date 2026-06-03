<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReadingStatus;
use App\Models\Comic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ComicImportService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function parseCSV(string $content): Collection
    {
        $lines = explode("\n", $content);
        $headers = array_map(fn ($h) => (string) $h, str_getcsv((string) array_shift($lines)));

        /** @var Collection<int, array<string, mixed>> $comics */
        $comics = collect();

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line);

            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, array_map(fn ($v) => $v === null ? null : (string) $v, $row));

            $comics->push($this->mapRowToComic($data));
        }

        return $comics;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function parseJson(string $content): Collection
    {
        $data = json_decode($content, true);

        if (! is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON: expected an array of comics.');
        }

        return collect($data)
            ->map(fn ($item) => $this->mapJsonToComic(is_array($item) ? $item : []))
            ->values();
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    protected function mapRowToComic(array $row): array
    {
        return [
            'title' => $row['Title'] ?? '',
            'publisher' => $row['Publisher'] ?? null,
            'start_year' => ! empty($row['Start Year']) ? (int) $row['Start Year'] : null,
            'issue_count' => ! empty($row['Issue Count']) ? (int) $row['Issue Count'] : null,
            'status' => $this->mapStatus($row['Status'] ?? ''),
            'rating' => $this->parseRating($row['Rating'] ?? ''),
            'date_started' => $this->parseDate($row['Date Started'] ?? ''),
            'date_finished' => $this->parseDate($row['Date Finished'] ?? ''),
            'notes' => $row['Notes'] ?? null,
            'review' => $row['Review'] ?? null,
            'creators' => $row['Creators'] ?? null,
            'characters' => $row['Characters'] ?? null,
            'comicvine_volume_id' => ! empty($row['ComicVine Volume ID']) ? $row['ComicVine Volume ID'] : null,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $item
     * @return array<string, mixed>
     */
    protected function mapJsonToComic(array $item): array
    {
        return [
            'title' => $item['title'] ?? '',
            'publisher' => $item['publisher'] ?? null,
            'start_year' => $this->toIntOrNull($item['start_year'] ?? null),
            'issue_count' => $this->toIntOrNull($item['issue_count'] ?? null),
            'status' => $this->mapStatus(is_string($item['status'] ?? null) ? $item['status'] : ''),
            'rating' => $this->parseRating($this->strOf($item['rating'] ?? null)),
            'date_started' => $this->parseDate(is_string($item['date_started'] ?? null) ? $item['date_started'] : ''),
            'date_finished' => $this->parseDate(is_string($item['date_finished'] ?? null) ? $item['date_finished'] : ''),
            'notes' => $item['notes'] ?? null,
            'review' => $item['review'] ?? null,
            'creators' => $item['creators'] ?? null,
            'characters' => $item['characters'] ?? null,
            'comicvine_volume_id' => $item['comicvine_volume_id'] ?? null,
            'cover_url' => $item['cover_url'] ?? null,
            'description' => $item['description'] ?? null,
            'comicvine_url' => $item['comicvine_url'] ?? null,
        ];
    }

    protected function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    protected function parseRating(?string $rating): ?int
    {
        if (empty($rating) || $rating === '0') {
            return null;
        }

        $rating = (int) $rating;

        return $rating >= 1 && $rating <= 5 ? $rating : null;
    }

    protected function mapStatus(string $status): ReadingStatus
    {
        return match (strtolower(trim($status))) {
            'read' => ReadingStatus::Read,
            'reading' => ReadingStatus::Reading,
            default => ReadingStatus::WantToRead,
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $comics
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function importComics(User $user, Collection $comics, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Pre-load existing identifiers for batch duplicate detection
        $existingIds = ['comicvine' => [], 'title_publisher' => []];
        if ($skipDuplicates) {
            $userComics = Comic::where('user_id', $user->id)
                ->select('comicvine_volume_id', 'title', 'publisher')
                ->get();
            $existingIds['comicvine'] = $userComics->pluck('comicvine_volume_id')->filter()->flip()->all();
            $existingIds['title_publisher'] = $userComics->map(function ($c) {
                return strtolower(trim($c->title)).':'.strtolower(trim($c->publisher ?? ''));
            })->flip()->all();
        }

        foreach ($comics as $index => $comicData) {
            try {
                if (empty($comicData['title'])) {
                    $errors[] = 'Row '.($index + 2).': Missing title';

                    continue;
                }

                if ($skipDuplicates && $this->isDuplicateFromCache($comicData, $existingIds)) {
                    $skipped++;

                    continue;
                }

                $status = $comicData['status'] ?? null;
                $comicData['status'] = $status instanceof ReadingStatus ? $status->value : $status;
                $comicData['user_id'] = $user->id;

                Comic::create($comicData);
                $imported++;

                // Update cache with newly imported comic
                if ($skipDuplicates) {
                    $cv = $comicData['comicvine_volume_id'] ?? null;
                    if (! empty($cv) && (is_int($cv) || is_string($cv))) {
                        $existingIds['comicvine'][$cv] = true;
                    }
                    $key = $this->titlePublisherKey($comicData);
                    $existingIds['title_publisher'][$key] = true;
                }
            } catch (\Exception $e) {
                $errors[] = 'Row '.($index + 2).': '.$e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $comicData
     * @param  array<string, array<array-key, mixed>>  $existingIds
     */
    protected function isDuplicateFromCache(array $comicData, array $existingIds): bool
    {
        $cv = $comicData['comicvine_volume_id'] ?? null;
        if (! empty($cv) && (is_int($cv) || is_string($cv)) && isset($existingIds['comicvine'][$cv])) {
            return true;
        }

        return isset($existingIds['title_publisher'][$this->titlePublisherKey($comicData)]);
    }

    /**
     * @param  array<string, mixed>  $comicData
     */
    protected function titlePublisherKey(array $comicData): string
    {
        return strtolower(trim($this->strOf($comicData['title'] ?? null))).':'.strtolower(trim($this->strOf($comicData['publisher'] ?? null)));
    }

    protected function toIntOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    protected function strOf(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
