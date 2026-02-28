<?php

use App\Models\Movie;
use App\Services\TmdbService;
use App\Models\User;

$service = app(TmdbService::class);
$user = User::where('name', 'dotmavriq')->first();

if (!$user) {
    echo "User dotmavriq not found.
";
    exit(1);
}

// 1. Process Movies and TV Series (Overwrite mode)
$entries = Movie::where('user_id', $user->id)
    ->where(function($q) {
        $q->where('title_type', '!=', 'TV Episode')
          ->orWhereNull('title_type');
    })
    ->whereNull('season_number')
    ->where(function($q) {
        $q->whereNull('metadata_fetched_at')
          ->orWhere('metadata_fetched_at', '<', now()->subHours(1));
    })
    ->get();

echo "Phase 1: Enriching " . $entries->count() . " Movies/Series (Overwrite mode)...
";

foreach ($entries as $index => $movie) {
    $current = $index + 1;
    echo "[$current/" . $entries->count() . "] {$movie->title}... ";
    
    $metadata = null;
    if (!empty($movie->imdb_id)) {
        $metadata = $service->findByImdbId($movie->imdb_id);
    }
    if (!$metadata && !empty($movie->title)) {
        $metadata = $service->searchByTitle($movie->title, $movie->year);
    }

    if ($metadata) {
        $movie->update([
            'description' => $metadata['description'] ?? $movie->description,
            'poster_url' => $metadata['poster_url'] ?? $movie->poster_url,
            'runtime_minutes' => $metadata['runtime_minutes'] ?? $movie->runtime_minutes,
            'release_date' => $metadata['release_date'] ?? $movie->release_date,
            'genres' => $metadata['genres'] ?? $movie->genres,
            'director' => $metadata['director'] ?? $movie->director,
            'metadata_fetched_at' => now(),
        ]);
        echo "Done.
";

        if (in_array($movie->title_type, ['TV Series', 'TV Mini Series']) && !empty($movie->poster_url)) {
             $titlePrefix = str_contains($movie->title, ':') ? trim(explode(':', $movie->title, 2)[0]) : $movie->title;
             Movie::propagateShowPoster($user->id, $movie->title, $titlePrefix, $movie->poster_url, $movie->title);
        }
    } else {
        echo "Not found.
";
        $movie->update(['metadata_fetched_at' => now()]);
    }
    
    usleep(200000); 
}

// 2. Process Episodes (Fill only mode)
$episodes = Movie::where('user_id', $user->id)
    ->where(function($q) {
        $q->where('title_type', 'TV Episode')
          ->orWhereNotNull('season_number');
    })
    ->where(function($q) {
        $q->whereNull('poster_url')
          ->orWhereNull('description');
    })
    ->get();

echo "
Phase 2: Filling missing data for " . $episodes->count() . " Episodes (Merge mode)...
";

foreach ($episodes as $index => $episode) {
    $current = $index + 1;
    echo "[$current/" . $episodes->count() . "] {$episode->title}... ";
    
    $metadata = null;
    if (!empty($episode->imdb_id)) {
        $metadata = $service->findEpisodeDetailsByImdbId($episode->imdb_id);
    }

    if ($metadata) {
        $episode->update([
            'description' => $episode->description ?? ($metadata['description'] ?? null),
            'poster_url' => $episode->poster_url ?? ($metadata['poster_url'] ?? null),
            'show_name' => $episode->show_name ?? ($metadata['show_name'] ?? null),
            'season_number' => $episode->season_number ?? ($metadata['season_number'] ?? null),
            'episode_number' => $episode->episode_number ?? ($metadata['episode_number'] ?? null),
            'metadata_fetched_at' => now(),
        ]);
        echo "Done.
";
    } else {
        echo "Skipped.
";
        $episode->update(['metadata_fetched_at' => now()]);
    }
    
    usleep(150000);
}

echo "
Enrichment complete!
";
