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
                'icon' => 'book-open',
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
        $books = $user->books();

        return [
            'currently_reading' => $books->clone()->where('status', ReadingStatus::Reading)->count(),
            'read_this_year' => $books->clone()
                ->where('status', ReadingStatus::Read)
                ->whereYear('date_finished', now()->year)
                ->count(),
            'total_books' => $books->count(),
        ];
    }

    public function getWatchingStats(): array
    {
        $user = Auth::user();
        $movies = $user->movies();

        return [
            'currently_watching' => $movies->clone()->where('status', WatchingStatus::Watching)->count(),
            'watched_this_year' => $movies->clone()
                ->where('status', WatchingStatus::Watched)
                ->whereYear('date_watched', now()->year)
                ->count(),
            'total_movies' => $movies->count(),
        ];
    }

    public function getAnimeStats(): array
    {
        $user = Auth::user();
        $anime = $user->anime();

        return [
            'currently_watching' => $anime->clone()->where('status', WatchingStatus::Watching)->count(),
            'watched_this_year' => $anime->clone()
                ->where('status', WatchingStatus::Watched)
                ->whereYear('date_finished', now()->year)
                ->count(),
            'total_anime' => $anime->count(),
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
