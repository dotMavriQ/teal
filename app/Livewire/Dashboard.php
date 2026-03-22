<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\PlayingStatus;
use App\Enums\ReadingStatus;
use App\Enums\WatchingStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function getCategories(): array
    {
        return [
            [
                'name' => 'Watching',
                'icon' => 'film',
                'description' => 'Movies, TV Shows, and Anime',
                'route' => 'watching.index',
                'active' => true,
                'color' => 'purple',
            ],
            [
                'name' => 'Reading',
                'icon' => 'squares-2x2',
                'description' => 'Books, Comics, Manga',
                'route' => 'reading.index',
                'active' => true,
                'color' => 'blue',
            ],
            [
                'name' => 'Playing',
                'icon' => 'puzzle-piece',
                'description' => 'Video Games',
                'route' => 'playing.index',
                'active' => true,
                'color' => 'green',
            ],
            [
                'name' => 'Listening',
                'icon' => 'musical-note',
                'description' => 'Music, Podcasts, Audiobooks',
                'route' => null,
                'active' => false,
                'color' => 'orange',
            ],
        ];
    }

    public function getReadingStats(): array
    {
        $user = Auth::user();
        $year = now()->year;
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $yearSql = $driver === 'pgsql' ? "CAST(EXTRACT(YEAR FROM %s) AS INTEGER)" : "strftime('%%Y', %s)";

        $bookStats = $user->books()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_reading", [ReadingStatus::Reading->value])
            ->selectRaw("SUM(CASE WHEN status = ? AND " . sprintf($yearSql, 'date_finished') . " = ? THEN 1 ELSE 0 END) as read_this_year", [ReadingStatus::Read->value, (string) $year])
            ->first();

        $comicStats = $user->comics()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_reading", [ReadingStatus::Reading->value])
            ->selectRaw("SUM(CASE WHEN status = ? AND " . sprintf($yearSql, 'date_finished') . " = ? THEN 1 ELSE 0 END) as read_this_year", [ReadingStatus::Read->value, (string) $year])
            ->first();

        return [
            'currently_reading' => (int) $bookStats->currently_reading + (int) $comicStats->currently_reading,
            'read_this_year' => (int) $bookStats->read_this_year + (int) $comicStats->read_this_year,
            'total_books' => (int) $bookStats->total,
            'total_comics' => (int) $comicStats->total,
        ];
    }

    public function getWatchingStats(): array
    {
        $user = Auth::user();
        $year = now()->year;
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $yearSql = $driver === 'pgsql' ? "CAST(EXTRACT(YEAR FROM %s) AS INTEGER)" : "strftime('%%Y', %s)";

        $stats = $user->movies()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_watching", [WatchingStatus::Watching->value])
            ->selectRaw("SUM(CASE WHEN status = ? AND " . sprintf($yearSql, 'date_watched') . " = ? THEN 1 ELSE 0 END) as watched_this_year", [WatchingStatus::Watched->value, (string) $year])
            ->first();

        return [
            'currently_watching' => (int) $stats->currently_watching,
            'watched_this_year' => (int) $stats->watched_this_year,
            'total_movies' => (int) $stats->total,
        ];
    }

    public function getAnimeStats(): array
    {
        $user = Auth::user();
        $year = now()->year;
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $yearSql = $driver === 'pgsql' ? "CAST(EXTRACT(YEAR FROM %s) AS INTEGER)" : "strftime('%%Y', %s)";

        $stats = $user->anime()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_watching", [WatchingStatus::Watching->value])
            ->selectRaw("SUM(CASE WHEN status = ? AND " . sprintf($yearSql, 'date_finished') . " = ? THEN 1 ELSE 0 END) as watched_this_year", [WatchingStatus::Watched->value, (string) $year])
            ->first();

        return [
            'currently_watching' => (int) $stats->currently_watching,
            'watched_this_year' => (int) $stats->watched_this_year,
            'total_anime' => (int) $stats->total,
        ];
    }

    public function getPlayingStats(): array
    {
        $user = Auth::user();

        $stats = $user->games()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_playing", [PlayingStatus::Playing->value])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as backlog", [PlayingStatus::Backlog->value])
            ->first();

        return [
            'total_games' => (int) $stats->total,
            'currently_playing' => (int) $stats->currently_playing,
            'backlog' => (int) $stats->backlog,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'categories' => $this->getCategories(),
            'readingStats' => $this->getReadingStats(),
            'watchingStats' => $this->getWatchingStats(),
            'animeStats' => $this->getAnimeStats(),
            'playingStats' => $this->getPlayingStats(),
        ])->layout('layouts.app');
    }
}
