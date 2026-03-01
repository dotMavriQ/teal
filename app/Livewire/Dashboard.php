<?php

declare(strict_types=1);

namespace App\Livewire;

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
                'description' => 'Video Games, Board Games',
                'route' => null,
                'active' => false,
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

        $bookStats = $user->books()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_reading", [ReadingStatus::Reading->value])
            ->selectRaw("SUM(CASE WHEN status = ? AND strftime('%Y', date_recorded) = ? THEN 1 ELSE 0 END) as read_this_year", [ReadingStatus::Read->value, (string) $year])
            ->first();

        $comicStats = $user->comics()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_reading", [ReadingStatus::Reading->value])
            ->selectRaw("SUM(CASE WHEN status = ? AND strftime('%Y', date_finished) = ? THEN 1 ELSE 0 END) as read_this_year", [ReadingStatus::Read->value, (string) $year])
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

        $stats = $user->movies()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_watching", [WatchingStatus::Watching->value])
            ->selectRaw("SUM(CASE WHEN status = ? AND strftime('%Y', date_watched) = ? THEN 1 ELSE 0 END) as watched_this_year", [WatchingStatus::Watched->value, (string) $year])
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

        $stats = $user->anime()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as currently_watching", [WatchingStatus::Watching->value])
            ->selectRaw("SUM(CASE WHEN status = ? AND strftime('%Y', date_finished) = ? THEN 1 ELSE 0 END) as watched_this_year", [WatchingStatus::Watched->value, (string) $year])
            ->first();

        return [
            'currently_watching' => (int) $stats->currently_watching,
            'watched_this_year' => (int) $stats->watched_this_year,
            'total_anime' => (int) $stats->total,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'categories' => $this->getCategories(),
            'readingStats' => $this->getReadingStats(),
            'watchingStats' => $this->getWatchingStats(),
            'animeStats' => $this->getAnimeStats(),
        ])->layout('layouts.app');
    }
}
