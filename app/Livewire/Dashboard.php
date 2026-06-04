<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\CollectionStatus;
use App\Enums\ListeningStatus;
use App\Enums\PlayingStatus;
use App\Enums\ReadingStatus;
use App\Enums\WatchingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * @return list<array<string, mixed>>
     */
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

    /**
     * @return array<string, int>
     */
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
            'currently_reading' => $this->intAttr($bookStats, 'currently_reading') + $this->intAttr($comicStats, 'currently_reading'),
            'read_this_year' => $this->intAttr($bookStats, 'read_this_year') + $this->intAttr($comicStats, 'read_this_year'),
            'total_books' => $this->intAttr($bookStats, 'total'),
            'total_comics' => $this->intAttr($comicStats, 'total'),
        ];
    }

    /**
     * @return array<string, int>
     */
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
            'currently_watching' => $this->intAttr($stats, 'currently_watching'),
            'watched_this_year' => $this->intAttr($stats, 'watched_this_year'),
            'total_movies' => $this->intAttr($stats, 'total'),
        ];
    }

    /**
     * @return array<string, int>
     */
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
            'currently_watching' => $this->intAttr($stats, 'currently_watching'),
            'watched_this_year' => $this->intAttr($stats, 'watched_this_year'),
            'total_anime' => $this->intAttr($stats, 'total'),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getPlayingStats(): array
    {
        $user = $this->currentUser();

        $stats = $user->games()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_playing', [PlayingStatus::Playing->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as backlog', [PlayingStatus::Backlog->value])
            ->first();

        return [
            'total_games' => $this->intAttr($stats, 'total'),
            'currently_playing' => $this->intAttr($stats, 'currently_playing'),
            'backlog' => $this->intAttr($stats, 'backlog'),
        ];
    }

    /**
     * @return array<string, int>
     */
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
            'total_concerts' => $this->intAttr($concertStats, 'total'),
            'attended' => $this->intAttr($concertStats, 'attended'),
            'upcoming' => $this->intAttr($concertStats, 'upcoming'),
            'total_albums' => $this->intAttr($albumStats, 'total'),
            'currently_listening' => $this->intAttr($albumStats, 'listening'),
        ];
    }

    #[Layout('layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.dashboard', [
            'categories' => $this->getCategories(),
            'readingStats' => $this->getReadingStats(),
            'watchingStats' => $this->getWatchingStats(),
            'animeStats' => $this->getAnimeStats(),
            'playingStats' => $this->getPlayingStats(),
            'listeningStats' => $this->getListeningStats(),
        ]);
    }

    private function currentUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }

    private function intAttr(?Model $model, string $key): int
    {
        $value = $model?->getAttribute($key);

        return is_numeric($value) ? (int) $value : 0;
    }
}
