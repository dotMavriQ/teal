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
    public function parseCSV(string $content): Collection
    {
        $lines = explode("\n", $content);
        $headers = str_getcsv(array_shift($lines));

        $comics = collect();

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line);

            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, $row);

            $comics->push($this->mapRowToComic($data));
        }

        return $comics;
    }

    public function parseJson(string $content): Collection
    {
        $data = json_decode($content, true);

        if (! is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON: expected an array of comics.');
        }

        return collect($data)->map(fn (array $item) => $this->mapJsonToComic($item));
    }

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

    protected function mapJsonToComic(array $item): array
    {
        return [
            'title' => $item['title'] ?? '',
            'publisher' => $item['publisher'] ?? null,
            'start_year' => isset($item['start_year']) ? (int) $item['start_year'] : null,
            'issue_count' => isset($item['issue_count']) ? (int) $item['issue_count'] : null,
            'status' => $this->mapStatus($item['status'] ?? ''),
            'rating' => $this->parseRating((string) ($item['rating'] ?? '')),
            'date_started' => $this->parseDate($item['date_started'] ?? ''),
            'date_finished' => $this->parseDate($item['date_finished'] ?? ''),
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

    public function importComics(User $user, Collection $comics, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Pre-load existing identifiers for batch duplicate detection
        $existingIds = [];
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

                $comicData['user_id'] = $user->id;
                $comicData['status'] = $comicData['status']->value;

                Comic::create($comicData);
                $imported++;

                // Update cache with newly imported comic
                if ($skipDuplicates) {
                    if (! empty($comicData['comicvine_volume_id'])) {
                        $existingIds['comicvine'][$comicData['comicvine_volume_id']] = true;
                    }
                    $key = strtolower(trim($comicData['title'])).':'.strtolower(trim($comicData['publisher'] ?? ''));
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

    protected function isDuplicateFromCache(array $comicData, array $existingIds): bool
    {
        if (! empty($comicData['comicvine_volume_id']) && isset($existingIds['comicvine'][$comicData['comicvine_volume_id']])) {
            return true;
        }

        $key = strtolower(trim($comicData['title'])).':'.strtolower(trim($comicData['publisher'] ?? ''));
        if (isset($existingIds['title_publisher'][$key])) {
            return true;
        }

        return false;
    }
}
