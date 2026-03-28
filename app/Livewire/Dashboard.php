<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\CollectionStatus;
use App\Enums\ListeningStatus;
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
                'icon' => 'book-open',
                'description' => 'Books, Comics, Manga',
                'route' => 'reading.index',
                'active' => true,
                'color' => 'blue',
            ],
            [
                'name' => 'Playing',
                'icon' => 'game-controller',
                'description' => 'Video Games & Board Games',
                'route' => 'playing.index',
                'active' => true,
                'color' => 'green',
            ],
            [
                'name' => 'Listening',
                'icon' => 'headphones',
                'description' => 'Concerts, Albums & Music',
                'route' => 'listening.index',
                'active' => true,
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

    public function getListeningStats(): array
    {
        $user = Auth::user();

        $concertStats = $user->concerts()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as attended", [ListeningStatus::Attended->value])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as upcoming", [ListeningStatus::Going->value])
            ->first();

        $albumStats = $user->albums()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as listening", [CollectionStatus::Listening->value])
            ->first();

        return [
            'total_concerts' => (int) $concertStats->total,
            'attended' => (int) $concertStats->attended,
            'upcoming' => (int) $concertStats->upcoming,
            'total_albums' => (int) $albumStats->total,
            'currently_listening' => (int) $albumStats->listening,
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
            'listeningStats' => $this->getListeningStats(),
        ])->layout('layouts.app');
    }
}
