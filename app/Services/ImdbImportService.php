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
     */
    public function parseCSV(string $content): Collection
    {
        $lines = explode("\n", $content);
        $headers = str_getcsv(array_shift($lines));

        if (! $this->hasRequiredHeaders($headers)) {
            throw new \InvalidArgumentException(
                'Invalid IMDB CSV format. Expected headers like: Const, Your Rating, Date Rated, Title, URL, Title Type, IMDb Rating, Runtime (mins), Year, Genres, Num Votes, Release Date, Directors'
            );
        }

        $entries = collect();

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line);

            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, $row);
            $entries->push($data);
        }

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
        $userRating = $this->parseRating($row['Your Rating'] ?? '');

        return [
            'type' => 'movie',
            'title' => trim($row['Title'] ?? ''),
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

                if ($skipDuplicates && $this->isDuplicateMovie($user, $movieData)) {
                    $skipped++;

                    continue;
                }

                $movieData['user_id'] = $user->id;
                $movieData['status'] = $movieData['status']->value;
                $movieData['date_added'] = now()->format('Y-m-d');
                unset($movieData['type']);

                $movie = Movie::create($movieData);
                $movieIds[] = $movie->id;
                $imported++;
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

                if ($skipDuplicates && $this->isDuplicateShow($user, $showData)) {
                    $skipped++;

                    continue;
                }

                $showData['user_id'] = $user->id;
                $showData['status'] = $showData['status']->value;
                $showData['date_added'] = now()->format('Y-m-d');
                unset($showData['type']);

                $show = Show::create($showData);
                $showIds[] = $show->id;
                $imported++;
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
                    if ($episodeData['season_number'] === null || $episodeData['episode_number'] === null) {
                        $errors[] = "Episode in show '$showName': Could not parse season/episode numbers";

                        continue;
                    }

                    if ($skipDuplicates && $this->isDuplicateEpisode($user, $show, $episodeData)) {
                        $skipped++;

                        continue;
                    }

                    $episodeData['user_id'] = $user->id;
                    $episodeData['show_id'] = $show->id;

                    // Use episode_title as title if available
                    if (empty($episodeData['episode_title'])) {
                        $episodeData['title'] = "S{$episodeData['season_number']}E{$episodeData['episode_number']}";
                    } else {
                        $episodeData['title'] = $episodeData['episode_title'];
                    }

                    unset($episodeData['type']);
                    unset($episodeData['show_name']);
                    unset($episodeData['episode_title']);

                    $episode = Episode::create($episodeData);
                    $episodeIds[] = $episode->id;
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Episode S{$episodeData['season_number']}E{$episodeData['episode_number']} of '$showName': " . $e->getMessage();
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

    protected function isDuplicateMovie(User $user, array $movieData): bool
    {
        $query = Movie::where('user_id', $user->id);

        if (! empty($movieData['imdb_id'])) {
            if ($query->clone()->where('imdb_id', $movieData['imdb_id'])->exists()) {
                return true;
            }
        }

        return false;
    }

    protected function isDuplicateShow(User $user, array $showData): bool
    {
        $query = Show::where('user_id', $user->id);

        if (! empty($showData['imdb_id'])) {
            if ($query->clone()->where('imdb_id', $showData['imdb_id'])->exists()) {
                return true;
            }
        }

        return false;
    }

    protected function isDuplicateEpisode(User $user, Show $show, array $episodeData): bool
    {
        return Episode::where('show_id', $show->id)
            ->where('user_id', $user->id)
            ->where('season_number', $episodeData['season_number'])
            ->where('episode_number', $episodeData['episode_number'])
            ->exists();
    }
