<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\CollectionStatus;
use App\Enums\ListeningStatus;
use App\Enums\PlayingStatus;
use App\Enums\ReadingStatus;
use App\Enums\WatchingStatus;
use App\Models\User;
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
        $user = $this->currentUser();
        $year = now()->year;
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $yearSql = $driver === 'pgsql' ? 'CAST(EXTRACT(YEAR FROM %s) AS INTEGER)' : "strftime('%%Y', %s)";

        $bookStats = $user->books()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_reading', [ReadingStatus::Reading->value])
            ->selectRaw('SUM(CASE WHEN status = ? AND '.sprintf($yearSql, 'date_finished').' = ? THEN 1 ELSE 0 END) as read_this_year', [ReadingStatus::Read->value, (string) $year])
            ->first();

        $comicStats = $user->comics()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_reading', [ReadingStatus::Reading->value])
            ->selectRaw('SUM(CASE WHEN status = ? AND '.sprintf($yearSql, 'date_finished').' = ? THEN 1 ELSE 0 END) as read_this_year', [ReadingStatus::Read->value, (string) $year])
            ->first();

        return [
            'currently_reading' => (int) ($bookStats?->getAttribute('currently_reading') ?? 0) + (int) ($comicStats?->getAttribute('currently_reading') ?? 0),
            'read_this_year' => (int) ($bookStats?->getAttribute('read_this_year') ?? 0) + (int) ($comicStats?->getAttribute('read_this_year') ?? 0),
            'total_books' => (int) ($bookStats?->getAttribute('total') ?? 0),
            'total_comics' => (int) ($comicStats?->getAttribute('total') ?? 0),
        ];
    }

    public function getWatchingStats(): array
    {
        $user = $this->currentUser();
        $year = now()->year;
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $yearSql = $driver === 'pgsql' ? 'CAST(EXTRACT(YEAR FROM %s) AS INTEGER)' : "strftime('%%Y', %s)";

        $stats = $user->movies()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_watching', [WatchingStatus::Watching->value])
            ->selectRaw('SUM(CASE WHEN status = ? AND '.sprintf($yearSql, 'date_watched').' = ? THEN 1 ELSE 0 END) as watched_this_year', [WatchingStatus::Watched->value, (string) $year])
            ->first();

        return [
            'currently_watching' => (int) ($stats?->getAttribute('currently_watching') ?? 0),
            'watched_this_year' => (int) ($stats?->getAttribute('watched_this_year') ?? 0),
            'total_movies' => (int) ($stats?->getAttribute('total') ?? 0),
        ];
    }

    public function getAnimeStats(): array
    {
        $user = $this->currentUser();
        $year = now()->year;
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $yearSql = $driver === 'pgsql' ? 'CAST(EXTRACT(YEAR FROM %s) AS INTEGER)' : "strftime('%%Y', %s)";

        $stats = $user->anime()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_watching', [WatchingStatus::Watching->value])
            ->selectRaw('SUM(CASE WHEN status = ? AND '.sprintf($yearSql, 'date_finished').' = ? THEN 1 ELSE 0 END) as watched_this_year', [WatchingStatus::Watched->value, (string) $year])
            ->first();

        return [
            'currently_watching' => (int) ($stats?->getAttribute('currently_watching') ?? 0),
            'watched_this_year' => (int) ($stats?->getAttribute('watched_this_year') ?? 0),
            'total_anime' => (int) ($stats?->getAttribute('total') ?? 0),
        ];
    }

    public function getPlayingStats(): array
    {
        $user = $this->currentUser();

        $stats = $user->games()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_playing', [PlayingStatus::Playing->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as backlog', [PlayingStatus::Backlog->value])
            ->first();

        return [
            'total_games' => (int) ($stats?->getAttribute('total') ?? 0),
            'currently_playing' => (int) ($stats?->getAttribute('currently_playing') ?? 0),
            'backlog' => (int) ($stats?->getAttribute('backlog') ?? 0),
        ];
    }

    public function getListeningStats(): array
    {
        $user = $this->currentUser();

        $concertStats = $user->concerts()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as attended', [ListeningStatus::Attended->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as upcoming', [ListeningStatus::Going->value])
            ->first();

        $albumStats = $user->albums()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as listening', [CollectionStatus::Listening->value])
            ->first();

        return [
            'total_concerts' => (int) ($concertStats?->getAttribute('total') ?? 0),
            'attended' => (int) ($concertStats?->getAttribute('attended') ?? 0),
            'upcoming' => (int) ($concertStats?->getAttribute('upcoming') ?? 0),
            'total_albums' => (int) ($albumStats?->getAttribute('total') ?? 0),
            'currently_listening' => (int) ($albumStats?->getAttribute('listening') ?? 0),
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

    private function currentUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }
}
