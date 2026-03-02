<?php
/**
 * Fix remaining ~72 TV Episodes that TMDB couldn't match by IMDb ID.
 * Matches by episode title against TMDB season/episode data.
 *
 * Usage: php artisan tinker scripts/fix-remaining-episodes.php
 *   OR:  cd /home/dotmavriq/Code/TEAL && php artisan tinker < scripts/fix-remaining-episodes.php
 */

use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Support\Str;

$tmdb = app(TmdbService::class);

// Shows with known TMDB IDs and the seasons we need
$shows = [
    ['name' => 'Cardinal',                      'tmdb_id' => 67743,  'seasons' => [4]],
    ['name' => 'Glória',                         'tmdb_id' => 109369, 'seasons' => [1]],
    ['name' => 'Reyka',                          'tmdb_id' => 132629, 'seasons' => [1]],
    ['name' => 'Our Girl',                       'tmdb_id' => 61517,  'seasons' => [3]],
    ['name' => 'Älskade Samir',                  'tmdb_id' => 289318, 'seasons' => [1]],
    ['name' => 'Once Upon a Time in Londongrad', 'tmdb_id' => 203226, 'seasons' => [1]],
    ['name' => 'Black Market: Dispatches',       'tmdb_id' => 67583,  'seasons' => [1]],
    ['name' => 'The Power of Nightmares',        'tmdb_id' => 6132,   'seasons' => [1]],
    ['name' => 'Evil Con Carne',                 'tmdb_id' => 4246,   'seasons' => [1, 2]],
    ['name' => 'NileCity 105.6',                 'tmdb_id' => 15232,  'seasons' => [1]],
    ['name' => 'Uppdrag granskning',             'tmdb_id' => 5848,   'seasons' => [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20]],
];

$totalFixed = 0;
$totalSkipped = 0;

foreach ($shows as $show) {
    $showName = $show['name'];
    $tmdbId = $show['tmdb_id'];

    // Find our unmatched episodes for this show
    $episodes = Movie::where('title_type', 'TV Episode')
        ->where(function ($q) use ($showName) {
            $q->where('show_name', $showName)
              ->orWhere('show_name', 'like', $showName . '%')
              ->orWhere('primary_title', 'like', '%' . $showName . '%')
              ->orWhere('primary_title', 'like', '%' . Str::ascii($showName) . '%');
        })
        ->where(function ($q) {
            $q->whereNull('season_number')
              ->orWhereNull('episode_number');
        })
        ->get();

    if ($episodes->isEmpty()) {
        echo "[$showName] No unmatched episodes found in DB, skipping.\n";
        continue;
    }

    echo "[$showName] Found {$episodes->count()} unmatched episodes in DB.\n";

    // Fetch all TMDB episodes for relevant seasons
    $tmdbEpisodes = [];
    foreach ($show['seasons'] as $seasonNum) {
        try {
            $seasonData = $tmdb->fetchTVSeasonEpisodes($tmdbId, $seasonNum);
            if (!empty($seasonData['episodes'])) {
                foreach ($seasonData['episodes'] as $ep) {
                    $tmdbEpisodes[] = [
                        'name'    => $ep['name'] ?? '',
                        'season'  => $seasonNum,
                        'episode' => $ep['episode_number'] ?? null,
                    ];
                }
            }
            usleep(300000); // rate limit
        } catch (\Exception $e) {
            echo "  Warning: Could not fetch S{$seasonNum}: {$e->getMessage()}\n";
        }
    }

    if (empty($tmdbEpisodes)) {
        echo "  No TMDB episodes fetched, skipping.\n";
        continue;
    }

    echo "  Fetched " . count($tmdbEpisodes) . " TMDB episodes across " . count($show['seasons']) . " season(s).\n";

    $fixed = 0;
    foreach ($episodes as $ep) {
        $ourTitle = trim($ep->primary_title);
        $matched = null;

        // Strategy 1: Exact title match
        foreach ($tmdbEpisodes as $te) {
            if (strcasecmp($ourTitle, $te['name']) === 0) {
                $matched = $te;
                break;
            }
        }

        // Strategy 2: Our title contains TMDB title or vice versa
        if (!$matched) {
            foreach ($tmdbEpisodes as $te) {
                if (empty($te['name'])) continue;
                if (Str::contains(strtolower($ourTitle), strtolower($te['name'])) ||
                    Str::contains(strtolower($te['name']), strtolower($ourTitle))) {
                    $matched = $te;
                    break;
                }
            }
        }

        // Strategy 3: Parse "Episode #X.Y" format from our title
        if (!$matched && preg_match('/Episode\s*#?(\d+)\.(\d+)/i', $ourTitle, $m)) {
            $parsedSeason = (int)$m[1];
            $parsedEp = (int)$m[2];
            foreach ($tmdbEpisodes as $te) {
                if ($te['season'] === $parsedSeason && $te['episode'] === $parsedEp) {
                    $matched = $te;
                    break;
                }
            }
        }

        // Strategy 4: Levenshtein distance (fuzzy match, threshold 3)
        if (!$matched) {
            $bestDist = 999;
            $bestMatch = null;
            foreach ($tmdbEpisodes as $te) {
                if (empty($te['name'])) continue;
                $dist = levenshtein(strtolower($ourTitle), strtolower($te['name']));
                if ($dist < $bestDist && $dist <= 3) {
                    $bestDist = $dist;
                    $bestMatch = $te;
                }
            }
            if ($bestMatch) {
                $matched = $bestMatch;
            }
        }

        if ($matched) {
            $ep->season_number = $matched['season'];
            $ep->episode_number = $matched['episode'];
            if (empty($ep->show_name)) {
                $ep->show_name = $showName;
            }
            $ep->save();
            $fixed++;
            echo "  FIXED: \"{$ourTitle}\" -> S{$matched['season']}E{$matched['episode']}\n";
        } else {
            $totalSkipped++;
            echo "  SKIP:  \"{$ourTitle}\" - no TMDB match\n";
        }
    }

    $totalFixed += $fixed;
    echo "  => Fixed {$fixed}/{$episodes->count()} episodes.\n\n";
}

// Also fix episodes with no show_name at all
echo "--- Checking for episodes with NULL show_name ---\n";
$noShowName = Movie::where('title_type', 'TV Episode')
    ->whereNull('show_name')
    ->where(function ($q) {
        $q->whereNull('season_number')
          ->orWhereNull('episode_number');
    })
    ->get();

if ($noShowName->isNotEmpty()) {
    echo "Found {$noShowName->count()} episodes with no show_name:\n";
    foreach ($noShowName as $ep) {
        echo "  ID={$ep->id} \"{$ep->primary_title}\" (IMDb: {$ep->imdb_id})\n";
    }
} else {
    echo "No orphan episodes with NULL show_name remain.\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total fixed: {$totalFixed}\n";
echo "Total skipped: {$totalSkipped}\n";
