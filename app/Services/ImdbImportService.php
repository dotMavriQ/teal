<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WatchingStatus;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Show;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ImdbImportService
{
    /**
     * Parse IMDb CSV export and categorize entries by type.
     * Uses fgetcsv() via a stream to properly handle multiline fields.
     *
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public function parseCSV(string $content): Collection
    {
        // Write content to a temporary stream
        $stream = fopen('php://memory', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('Could not open a memory stream to parse the CSV.');
        }
        fwrite($stream, $content);
        rewind($stream);

        // Read headers
        $headerRow = fgetcsv($stream, null, ',', '"', '\\');

        if ($headerRow === false || ! $this->hasRequiredHeaders($headerRow)) {
            fclose($stream);
            throw new \InvalidArgumentException(
                'Invalid IMDB CSV format. Expected headers like: Const, Your Rating, Date Rated, Title, URL, Title Type, IMDb Rating, Runtime (mins), Year, Genres, Num Votes, Release Date, Directors'
            );
        }

        $headers = array_map(fn ($h) => (string) $h, $headerRow);

        /** @var Collection<int, array<string, string|null>> $entries */
        $entries = collect();

        // Read all rows
        while (($row = fgetcsv($stream, null, ',', '"', '\\')) !== false) {
            // Skip empty rows
            if (count($row) === 1 && ($row[0] === null || $row[0] === '')) {
                continue;
            }

            // Ensure row has correct number of columns
            if (count($row) !== count($headers)) {
                continue;
            }

            $entries->push(array_combine($headers, array_map(fn ($v) => $v === null ? null : (string) $v, $row)));
        }

        fclose($stream);

        // Process and categorize entries
        return $this->categorizeEntries($entries);
    }

    /**
     * @param  array<array-key, mixed>  $headers
     */
    protected function hasRequiredHeaders(array $headers): bool
    {
        foreach (['Const', 'Title'] as $header) {
            if (! in_array($header, $headers, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Categorize raw CSV entries into movies, shows, and episodes.
     *
     * @param  Collection<int, array<string, string|null>>  $rawEntries
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    protected function categorizeEntries(Collection $rawEntries): Collection
    {
        /** @var Collection<int, array<string, mixed>> $movies */
        $movies = collect();
        /** @var Collection<int, array<string, mixed>> $shows */
        $shows = collect();
        /** @var Collection<int, array<string, mixed>> $episodes */
        $episodes = collect();

        foreach ($rawEntries as $row) {
            $titleType = trim($row['Title Type'] ?? '');

            match ($titleType) {
                'Movie', 'TV Movie' => $movies->push($this->mapRowToMovie($row)),
                'TV Series', 'TV Mini Series' => $shows->push($this->mapRowToShow($row)),
                'TV Episode' => $episodes->push($this->mapRowToEpisode($row)),
                default => null,
            };
        }

        return collect([
            'movies' => $movies,
            'shows' => $shows,
            'episodes' => $episodes,
        ]);
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    protected function mapRowToMovie(array $row): array
    {
        $title = trim($row['Title'] ?? '');
        $userRating = $this->parseRating($row['Your Rating'] ?? '');

        // Check if this movie is actually an episode
        $episodeInfo = $this->parseEpisodeString($title);
        $isEpisode = $episodeInfo['season_number'] !== null && $episodeInfo['episode_number'] !== null;

        $mapped = [
            'type' => 'movie',
            'title' => $title,
            'original_title' => ! empty($row['Original Title']) ? trim($row['Original Title']) : null,
            'director' => ! empty($row['Directors']) ? trim($row['Directors']) : null,
            'imdb_id' => ! empty($row['Const']) ? trim($row['Const']) : null,
            'imdb_url' => ! empty($row['URL']) ? trim($row['URL']) : null,
            'title_type' => ! empty($row['Title Type']) ? trim($row['Title Type']) : null,
            'year' => $this->parseYear($row['Year'] ?? ''),
            'runtime_minutes' => $this->parseRuntime($row['Runtime (mins)'] ?? ''),
            'genres' => ! empty($row['Genres']) ? trim($row['Genres']) : null,
            'imdb_rating' => $this->parseImdbRating($row['IMDb Rating'] ?? ''),
            'num_votes' => $this->parseNumVotes($row['Num Votes'] ?? ''),
            'release_date' => $this->parseDate($row['Release Date'] ?? ''),
            'date_rated' => $this->parseDate($row['Date Rated'] ?? ''),
            'rating' => $userRating,
            'status' => $userRating ? WatchingStatus::Watched : WatchingStatus::Watchlist,
            'date_watched' => $userRating ? $this->parseDate($row['Date Rated'] ?? '') : null,
        ];

        // If it's actually an episode, add episode fields
        if ($isEpisode) {
            $mapped['show_name'] = $episodeInfo['show_name'];
            $mapped['season_number'] = $episodeInfo['season_number'];
            $mapped['episode_number'] = $episodeInfo['episode_number'];
        }

        return $mapped;
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    protected function mapRowToShow(array $row): array
    {
        $userRating = $this->parseRating($row['Your Rating'] ?? '');

        return [
            'type' => 'show',
            'title' => trim($row['Title'] ?? ''),
            'original_title' => ! empty($row['Original Title']) ? trim($row['Original Title']) : null,
            'imdb_id' => ! empty($row['Const']) ? trim($row['Const']) : null,
            'imdb_url' => ! empty($row['URL']) ? trim($row['URL']) : null,
            'title_type' => ! empty($row['Title Type']) ? trim($row['Title Type']) : null,
            'year' => $this->parseYear($row['Year'] ?? ''),
            'genres' => ! empty($row['Genres']) ? trim($row['Genres']) : null,
            'imdb_rating' => $this->parseImdbRating($row['IMDb Rating'] ?? ''),
            'num_votes' => $this->parseNumVotes($row['Num Votes'] ?? ''),
            'release_date' => $this->parseDate($row['Release Date'] ?? ''),
            'rating' => $userRating,
            'status' => $userRating ? WatchingStatus::Watched : WatchingStatus::Watchlist,
        ];
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    protected function mapRowToEpisode(array $row): array
    {
        $title = trim($row['Title'] ?? '');
        $episodeInfo = $this->parseEpisodeString($title);
        $userRating = $this->parseRating($row['Your Rating'] ?? '');

        return [
            'type' => 'episode',
            'show_name' => $episodeInfo['show_name'],
            'season_number' => $episodeInfo['season_number'],
            'episode_number' => $episodeInfo['episode_number'],
            'episode_title' => $episodeInfo['episode_title'],
            'imdb_id' => ! empty($row['Const']) ? trim($row['Const']) : null,
            'imdb_url' => ! empty($row['URL']) ? trim($row['URL']) : null,
            'director' => ! empty($row['Directors']) ? trim($row['Directors']) : null,
            'runtime_minutes' => $this->parseRuntime($row['Runtime (mins)'] ?? ''),
            'genres' => ! empty($row['Genres']) ? trim($row['Genres']) : null,
            'imdb_rating' => $this->parseImdbRating($row['IMDb Rating'] ?? ''),
            'num_votes' => $this->parseNumVotes($row['Num Votes'] ?? ''),
            'release_date' => $this->parseDate($row['Release Date'] ?? ''),
            'date_rated' => $this->parseDate($row['Date Rated'] ?? ''),
            'year' => $this->parseYear($row['Year'] ?? ''),
            'rating' => $userRating,
            'date_watched' => $userRating ? $this->parseDate($row['Date Rated'] ?? '') : null,
        ];
    }

    /**
     * Parse episode string like "Show Name: Episode #1.6" or "Show Name: Episode #S01E06".
     *
     * @return array{show_name: string, season_number: int|null, episode_number: int|null, episode_title: string|null}
     */
    protected function parseEpisodeString(string $fullTitle): array
    {
        $patterns = [
            '/^(.+?):\s+Episode\s+#(\d+)\.(\d+)(?:\s+"(.+)")?$/i',
            '/^(.+?):\s+Episode\s+#S(\d+)E(\d+)(?:\s+"(.+)")?$/i',
            '/^(.+?)\s+S(\d+)E(\d+)(?:\s+"(.+)")?$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $fullTitle, $matches)) {
                return [
                    'show_name' => trim($matches[1]),
                    'season_number' => (int) $matches[2],
                    'episode_number' => (int) $matches[3],
                    'episode_title' => ! empty($matches[4]) ? trim($matches[4]) : null,
                ];
            }
        }

        // Try to parse "Show Name: Episode Title" format (no season/episode numbers)
        if (preg_match('/^(.+?):\s+(.+)$/i', $fullTitle, $matches)) {
            return [
                'show_name' => trim($matches[1]),
                'season_number' => null,
                'episode_number' => null,
                'episode_title' => trim($matches[2]),
            ];
        }

        return [
            'show_name' => $fullTitle,
            'season_number' => null,
            'episode_number' => null,
            'episode_title' => null,
        ];
    }

    protected function parseYear(?string $year): ?int
    {
        if (empty($year) || ! is_numeric($year)) {
            return null;
        }

        $y = (int) $year;

        return ($y >= 1888 && $y <= 2100) ? $y : null;
    }

    protected function parseRuntime(?string $runtime): ?int
    {
        if (empty($runtime) || ! is_numeric($runtime)) {
            return null;
        }

        $r = (int) $runtime;

        return $r > 0 ? $r : null;
    }

    protected function parseRating(?string $rating): ?int
    {
        if (empty($rating) || $rating === '0') {
            return null;
        }

        $r = (int) $rating;

        return ($r >= 1 && $r <= 10) ? $r : null;
    }

    protected function parseImdbRating(?string $rating): ?float
    {
        if (empty($rating) || ! is_numeric($rating)) {
            return null;
        }

        $r = (float) $rating;

        return ($r >= 0 && $r <= 10) ? $r : null;
    }

    protected function parseNumVotes(?string $votes): ?int
    {
        if (empty($votes) || ! is_numeric($votes)) {
            return null;
        }

        return (int) $votes;
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

    /**
     * @param  Collection<string, Collection<int, array<string, mixed>>>  $categorized
     * @return array<string, mixed>
     */
    public function importAll(User $user, Collection $categorized, bool $skipDuplicates = true): array
    {
        // Pre-load existing IMDb IDs for batch duplicate detection
        $existingMovieImdbIds = [];
        $existingShowImdbIds = [];
        if ($skipDuplicates) {
            $existingMovieImdbIds = Movie::where('user_id', $user->id)
                ->whereNotNull('imdb_id')
                ->pluck('id', 'imdb_id')
                ->all();
            $existingShowImdbIds = Show::where('user_id', $user->id)
                ->whereNotNull('imdb_id')
                ->pluck('id', 'imdb_id')
                ->all();
        }

        $results = [
            'movies' => $this->importMovies($user, $this->bucket($categorized, 'movies'), $skipDuplicates, $existingMovieImdbIds),
            'shows' => $this->importShows($user, $this->bucket($categorized, 'shows'), $skipDuplicates, $existingShowImdbIds),
            'episodes' => $this->importEpisodes($user, $this->bucket($categorized, 'episodes'), $skipDuplicates),
        ];

        // Calculate totals
        return [
            'imported' => $results['movies']['imported'] + $results['shows']['imported'] + $results['episodes']['imported'],
            'skipped' => $results['movies']['skipped'] + $results['shows']['skipped'] + $results['episodes']['skipped'],
            'errors' => array_merge(
                $results['movies']['errors'],
                $results['shows']['errors'],
                $results['episodes']['errors']
            ),
            'details' => $results,
        ];
    }

    /**
     * @param  Collection<string, Collection<int, array<string, mixed>>>  $categorized
     * @return Collection<int, array<string, mixed>>
     */
    protected function bucket(Collection $categorized, string $key): Collection
    {
        $bucket = $categorized->get($key);

        if ($bucket instanceof Collection) {
            return $bucket;
        }

        /** @var Collection<int, array<string, mixed>> */
        return collect();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $movies
     * @param  array<array-key, mixed>  $existingImdbIds
     * @return array{imported: int, skipped: int, errors: list<string>, ids: list<int>}
     */
    public function importMovies(User $user, Collection $movies, bool $skipDuplicates = true, array $existingImdbIds = []): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $movieIds = [];

        if ($existingImdbIds === [] && $skipDuplicates) {
            $existingImdbIds = Movie::where('user_id', $user->id)
                ->whereNotNull('imdb_id')
                ->pluck('id', 'imdb_id')
                ->all();
        }

        foreach ($movies as $movieData) {
            try {
                if (empty($movieData['title'])) {
                    $errors[] = 'Movie Row: Missing title';

                    continue;
                }

                $imdbKey = $this->scalarKey($movieData['imdb_id'] ?? null);
                $existingId = $imdbKey !== null ? ($existingImdbIds[$imdbKey] ?? null) : null;
                $existingMovie = (is_int($existingId) || is_string($existingId)) ? Movie::find($existingId) : null;

                if ($existingMovie) {
                    // Non-destructive update: only fill empty fields
                    if ($this->updateWithEmptyFieldsOnly($existingMovie, $movieData)) {
                        $existingMovie->save();
                        $movieIds[] = $existingMovie->id;
                        $imported++;
                    } else {
                        $skipped++;
                    }
                } else {
                    // Create new movie
                    $status = $movieData['status'] ?? null;
                    $movieData['status'] = $status instanceof WatchingStatus ? $status->value : $status;
                    $movieData['user_id'] = $user->id;
                    $movieData['date_added'] = now()->format('Y-m-d');
                    unset($movieData['type']);

                    $movie = Movie::create($movieData);
                    $movieIds[] = $movie->id;
                    $imported++;

                    if ($imdbKey !== null) {
                        $existingImdbIds[$imdbKey] = $movie->id;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = 'Movie "'.$this->strOf($movieData['title']).'": '.$e->getMessage();
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors, 'ids' => $movieIds];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $shows
     * @param  array<array-key, mixed>  $existingImdbIds
     * @return array{imported: int, skipped: int, errors: list<string>, ids: list<int>}
     */
    public function importShows(User $user, Collection $shows, bool $skipDuplicates = true, array $existingImdbIds = []): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $showIds = [];

        if ($existingImdbIds === [] && $skipDuplicates) {
            $existingImdbIds = Show::where('user_id', $user->id)
                ->whereNotNull('imdb_id')
                ->pluck('id', 'imdb_id')
                ->all();
        }

        foreach ($shows as $showData) {
            try {
                if (empty($showData['title'])) {
                    $errors[] = 'Show Row: Missing title';

                    continue;
                }

                $imdbKey = $this->scalarKey($showData['imdb_id'] ?? null);
                $existingId = $imdbKey !== null ? ($existingImdbIds[$imdbKey] ?? null) : null;
                $existingShow = (is_int($existingId) || is_string($existingId)) ? Show::find($existingId) : null;

                if ($existingShow) {
                    if ($this->updateWithEmptyFieldsOnly($existingShow, $showData)) {
                        $existingShow->save();
                        $showIds[] = $existingShow->id;
                        $imported++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $status = $showData['status'] ?? null;
                    $showData['status'] = $status instanceof WatchingStatus ? $status->value : $status;
                    $showData['user_id'] = $user->id;
                    $showData['date_added'] = now()->format('Y-m-d');
                    unset($showData['type']);

                    $show = Show::create($showData);
                    $showIds[] = $show->id;
                    $imported++;

                    if ($imdbKey !== null) {
                        $existingImdbIds[$imdbKey] = $show->id;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = 'Show "'.$this->strOf($showData['title']).'": '.$e->getMessage();
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors, 'ids' => $showIds];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $episodes
     * @return array{imported: int, skipped: int, errors: list<string>, ids: list<int>}
     */
    public function importEpisodes(User $user, Collection $episodes, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $episodeIds = [];

        // Group episodes by show name
        $episodesByShow = $episodes->groupBy('show_name');

        foreach ($episodesByShow as $showName => $showEpisodes) {
            $showName = $this->strOf($showName);

            // Find or create the parent show
            $show = $this->findOrCreateShow($user, $showName);

            if (! $show) {
                $errors[] = "Failed to create show placeholder for '$showName'";

                continue;
            }

            foreach ($showEpisodes as $episodeData) {
                try {
                    $existingEpisode = $this->findExistingEpisode($user, $show, $episodeData);

                    if ($existingEpisode) {
                        if ($this->updateWithEmptyFieldsOnly($existingEpisode, $episodeData)) {
                            $existingEpisode->save();
                            $episodeIds[] = $existingEpisode->id;
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        // Create new episode
                        $episodeData['user_id'] = $user->id;
                        $episodeData['show_id'] = $show->id;

                        $season = $episodeData['season_number'] ?? null;
                        $episodeNum = $episodeData['episode_number'] ?? null;
                        if (empty($episodeData['episode_title'])) {
                            $episodeData['title'] = ($season !== null && $episodeNum !== null)
                                ? 'S'.$this->strOf($season).'E'.$this->strOf($episodeNum)
                                : 'Untitled';
                        } else {
                            $episodeData['title'] = $episodeData['episode_title'];
                        }

                        unset($episodeData['type'], $episodeData['show_name'], $episodeData['episode_title']);

                        $episode = Episode::create($episodeData);
                        $episodeIds[] = $episode->id;
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $season = $episodeData['season_number'] ?? null;
                    $episodeNum = $episodeData['episode_number'] ?? null;
                    $seasonEp = ($season !== null && $episodeNum !== null)
                        ? 'S'.$this->strOf($season).'E'.$this->strOf($episodeNum)
                        : ($this->strOf($episodeData['episode_title'] ?? null) ?: 'Unknown');
                    $errors[] = "Episode '$seasonEp' of '$showName': ".$e->getMessage();
                }
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors, 'ids' => $episodeIds];
    }

    /**
     * Find or create a placeholder show if it doesn't exist.
     */
    protected function findOrCreateShow(User $user, string $showName): ?Show
    {
        $show = Show::where('user_id', $user->id)
            ->where('title', $showName)
            ->first();

        if ($show) {
            return $show;
        }

        // Create placeholder show
        try {
            return Show::create([
                'user_id' => $user->id,
                'title' => $showName,
                'status' => WatchingStatus::Watchlist->value,
                'date_added' => now()->format('Y-m-d'),
            ]);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $episodeData
     */
    protected function findExistingEpisode(User $user, Show $show, array $episodeData): ?Episode
    {
        // First, try to find by imdb_id if available
        if (! empty($episodeData['imdb_id'])) {
            $episode = Episode::where('show_id', $show->id)
                ->where('user_id', $user->id)
                ->where('imdb_id', $episodeData['imdb_id'])
                ->first();

            if ($episode) {
                return $episode;
            }
        }

        // If we have season/episode numbers, try to find by those
        if (($episodeData['season_number'] ?? null) !== null && ($episodeData['episode_number'] ?? null) !== null) {
            return Episode::where('show_id', $show->id)
                ->where('user_id', $user->id)
                ->where('season_number', $episodeData['season_number'])
                ->where('episode_number', $episodeData['episode_number'])
                ->first();
        }

        // For episodes without season/episode numbers, try to find by title
        if (! empty($episodeData['episode_title'])) {
            return Episode::where('show_id', $show->id)
                ->where('user_id', $user->id)
                ->where('title', $episodeData['episode_title'])
                ->first();
        }

        return null;
    }

    /**
     * Update model with data, but only fill fields that are currently empty (non-destructive).
     * Returns true if any updates were made.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateWithEmptyFieldsOnly(Model $model, array $data): bool
    {
        $updated = false;

        foreach ($data as $key => $value) {
            // Skip special keys
            if (in_array($key, ['type', 'show_name', 'episode_title', 'status'], true)) {
                continue;
            }

            // Only update if the field is empty and new value is not empty
            if (empty($model->$key) && ! empty($value)) {
                $model->$key = $value;
                $updated = true;
            }
        }

        return $updated;
    }

    protected function scalarKey(mixed $value): int|string|null
    {
        if (empty($value)) {
            return null;
        }

        return is_int($value) || is_string($value) ? $value : null;
    }

    protected function strOf(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
