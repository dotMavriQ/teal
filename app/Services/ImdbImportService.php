<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WatchingStatus;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Show;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ImdbImportService
{
    /**
     * Parse IMDb CSV export and categorize entries by type
     * Uses fgetcsv() via a stream to properly handle multiline fields
     */
    public function parseCSV(string $content): Collection
    {
        // Write content to a temporary stream
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        // Read headers
        $headers = fgetcsv($stream, null, ',', '"', '\\');

        if (! $headers || ! $this->hasRequiredHeaders($headers)) {
            fclose($stream);
            throw new \InvalidArgumentException(
                'Invalid IMDB CSV format. Expected headers like: Const, Your Rating, Date Rated, Title, URL, Title Type, IMDb Rating, Runtime (mins), Year, Genres, Num Votes, Release Date, Directors'
            );
        }

        $entries = collect();

        // Read all rows
        while (($row = fgetcsv($stream, null, ',', '"', '\\')) !== false) {
            // Skip empty rows
            if (! $row || (count($row) === 1 && empty($row[0]))) {
                continue;
            }

            // Ensure row has correct number of columns
            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, $row);
            $entries->push($data);
        }

        fclose($stream);

        // Process and categorize entries
        return $this->categorizeEntries($entries);
    }

    protected function hasRequiredHeaders(array $headers): bool
    {
        $required = ['Const', 'Title'];

        foreach ($required as $header) {
            if (! in_array($header, $headers)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Categorize raw CSV entries into movies, shows, and episodes
     */
    protected function categorizeEntries(Collection $rawEntries): Collection
    {
        $categorized = collect([
            'movies' => collect(),
            'shows' => collect(),
            'episodes' => collect(),
        ]);

        foreach ($rawEntries as $row) {
            $titleType = trim($row['Title Type'] ?? '');

            match ($titleType) {
                'Movie', 'TV Movie' => $categorized['movies']->push($this->mapRowToMovie($row)),
                'TV Series', 'TV Mini Series' => $categorized['shows']->push($this->mapRowToShow($row)),
                'TV Episode' => $categorized['episodes']->push($this->mapRowToEpisode($row)),
                default => null,
            };
        }

        return $categorized;
    }

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
     * Parse episode string like "Show Name: Episode #1.6" or "Show Name: Episode #S01E06"
     * Returns array with show_name, season_number, episode_number, and episode_title
     */
    protected function parseEpisodeString(string $fullTitle): array
    {
        $result = [
            'show_name' => $fullTitle,
            'season_number' => null,
            'episode_number' => null,
            'episode_title' => null,
        ];

        // Try to parse "Show Name: Episode #1.6" format
        if (preg_match('/^(.+?):\s+Episode\s+#(\d+)\.(\d+)(?:\s+"(.+)")?$/i', $fullTitle, $matches)) {
            $result['show_name'] = trim($matches[1]);
            $result['season_number'] = (int) $matches[2];
            $result['episode_number'] = (int) $matches[3];
            $result['episode_title'] = ! empty($matches[4]) ? trim($matches[4]) : null;

            return $result;
        }

        // Try to parse "Show Name: Episode #S01E06" format
        if (preg_match('/^(.+?):\s+Episode\s+#S(\d+)E(\d+)(?:\s+"(.+)")?$/i', $fullTitle, $matches)) {
            $result['show_name'] = trim($matches[1]);
            $result['season_number'] = (int) $matches[2];
            $result['episode_number'] = (int) $matches[3];
            $result['episode_title'] = ! empty($matches[4]) ? trim($matches[4]) : null;

            return $result;
        }

        // Try to parse alternative format "Show Name S01E06" without colon
        if (preg_match('/^(.+?)\s+S(\d+)E(\d+)(?:\s+"(.+)")?$/i', $fullTitle, $matches)) {
            $result['show_name'] = trim($matches[1]);
            $result['season_number'] = (int) $matches[2];
            $result['episode_number'] = (int) $matches[3];
            $result['episode_title'] = ! empty($matches[4]) ? trim($matches[4]) : null;

            return $result;
        }

        // Try to parse "Show Name: Episode Title" format (no season/episode numbers)
        if (preg_match('/^(.+?):\s+(.+)$/i', $fullTitle, $matches)) {
            $result['show_name'] = trim($matches[1]);
            $result['episode_title'] = trim($matches[2]);

            return $result;
        }

        return $result;
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

    public function importAll(User $user, Collection $categorized, bool $skipDuplicates = true): array
    {
        $results = [
            'movies' => $this->importMovies($user, $categorized['movies'] ?? collect(), $skipDuplicates),
            'shows' => $this->importShows($user, $categorized['shows'] ?? collect(), $skipDuplicates),
            'episodes' => $this->importEpisodes($user, $categorized['episodes'] ?? collect(), $skipDuplicates),
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

    public function importMovies(User $user, Collection $movies, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $movieIds = [];

        foreach ($movies as $index => $movieData) {
            try {
                if (empty($movieData['title'])) {
                    $errors[] = 'Movie Row: Missing title';

                    continue;
                }

                $existingMovie = $this->findExistingMovie($user, $movieData);

                if ($existingMovie) {
                    // Non-destructive update: only fill empty fields
                    $updated = $this->updateWithEmptyFieldsOnly($existingMovie, $movieData);
                    if ($updated) {
                        $existingMovie->save();
                        $movieIds[] = $existingMovie->id;
                        $imported++;
                    } else {
                        $skipped++;
                    }
                } else {
                    // Create new movie
                    $movieData['user_id'] = $user->id;
                    $movieData['status'] = $movieData['status']->value;
                    $movieData['date_added'] = now()->format('Y-m-d');
                    unset($movieData['type']);

                    $movie = Movie::create($movieData);
                    $movieIds[] = $movie->id;
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = 'Movie "' . ($movieData['title'] ?? 'Unknown') . '": ' . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'ids' => $movieIds,
        ];
    }

    public function importShows(User $user, Collection $shows, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $showIds = [];

        foreach ($shows as $index => $showData) {
            try {
                if (empty($showData['title'])) {
                    $errors[] = 'Show Row: Missing title';

                    continue;
                }

                $existingShow = $this->findExistingShow($user, $showData);

                if ($existingShow) {
                    // Non-destructive update: only fill empty fields
                    $updated = $this->updateWithEmptyFieldsOnly($existingShow, $showData);
                    if ($updated) {
                        $existingShow->save();
                        $showIds[] = $existingShow->id;
                        $imported++;
                    } else {
                        $skipped++;
                    }
                } else {
                    // Create new show
                    $showData['user_id'] = $user->id;
                    $showData['status'] = $showData['status']->value;
                    $showData['date_added'] = now()->format('Y-m-d');
                    unset($showData['type']);

                    $show = Show::create($showData);
                    $showIds[] = $show->id;
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = 'Show "' . ($showData['title'] ?? 'Unknown') . '": ' . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'ids' => $showIds,
        ];
    }

    public function importEpisodes(User $user, Collection $episodes, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $episodeIds = [];

        // Group episodes by show name
        $episodesByShow = $episodes->groupBy('show_name');

        foreach ($episodesByShow as $showName => $showEpisodes) {
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
                        // Non-destructive update: only fill empty fields
                        $updated = $this->updateWithEmptyFieldsOnly($existingEpisode, $episodeData);
                        if ($updated) {
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

                        // Use episode_title as title if available, otherwise construct from season/episode
                        if (empty($episodeData['episode_title'])) {
                            if ($episodeData['season_number'] !== null && $episodeData['episode_number'] !== null) {
                                $episodeData['title'] = "S{$episodeData['season_number']}E{$episodeData['episode_number']}";
                            } else {
                                $episodeData['title'] = 'Untitled';
                            }
                        } else {
                            $episodeData['title'] = $episodeData['episode_title'];
                        }

                        unset($episodeData['type']);
                        unset($episodeData['show_name']);
                        unset($episodeData['episode_title']);

                        $episode = Episode::create($episodeData);
                        $episodeIds[] = $episode->id;
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $seasonEp = ($episodeData['season_number'] !== null && $episodeData['episode_number'] !== null)
                        ? "S{$episodeData['season_number']}E{$episodeData['episode_number']}"
                        : ($episodeData['episode_title'] ?? 'Unknown');
                    $errors[] = "Episode '$seasonEp' of '$showName': " . $e->getMessage();
                }
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'ids' => $episodeIds,
        ];
    }

    /**
     * Find or create a placeholder show if it doesn't exist
     */
    protected function findOrCreateShow(User $user, string $showName): ?Show
    {
        // Try to find existing show
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

    protected function findExistingMovie(User $user, array $movieData): ?Movie
    {
        if (! empty($movieData['imdb_id'])) {
            return Movie::where('user_id', $user->id)
                ->where('imdb_id', $movieData['imdb_id'])
                ->first();
        }

        return null;
    }

    protected function findExistingShow(User $user, array $showData): ?Show
    {
        if (! empty($showData['imdb_id'])) {
            return Show::where('user_id', $user->id)
                ->where('imdb_id', $showData['imdb_id'])
                ->first();
        }

        return null;
    }

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
        if ($episodeData['season_number'] !== null && $episodeData['episode_number'] !== null) {
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
     * Update model with data, but only fill fields that are currently empty (non-destructive)
     * Returns true if any updates were made
     */
    protected function updateWithEmptyFieldsOnly($model, array $data): bool
    {
        $updated = false;

        foreach ($data as $key => $value) {
            // Skip special keys
            if (in_array($key, ['type', 'show_name', 'episode_title', 'status'])) {
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

    protected function isDuplicateMovie(User $user, array $movieData): bool
    {
        return $this->findExistingMovie($user, $movieData) !== null;
    }

    protected function isDuplicateShow(User $user, array $showData): bool
    {
        return $this->findExistingShow($user, $showData) !== null;
    }

    protected function isDuplicateEpisode(User $user, Show $show, array $episodeData): bool
    {
        return $this->findExistingEpisode($user, $show, $episodeData) !== null;
    }}
