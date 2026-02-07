<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Enums\WatchingStatus;
use App\Models\Movie;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class MovieShow extends Component
{
    use AuthorizesRequests;

    public Movie $movie;

    public function mount(Movie $movie): void
    {
        $this->authorize('view', $movie);
        $this->movie = $movie;
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->movie);

        $updates = ['status' => $status];

        if ($status === 'watched' && ! $this->movie->date_watched) {
            $updates['date_watched'] = now();
        }

        $this->movie->update($updates);
        $this->movie->refresh();
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->movie);

        $this->movie->update([
            'rating' => $rating,
            'date_rated' => now(),
        ]);
        $this->movie->refresh();
    }

    public function deleteMovie(): void
    {
        $this->authorize('delete', $this->movie);

        $this->movie->delete();

        session()->flash('message', 'Movie deleted successfully.');

        $this->redirect(route('movies.index'));
    }

    public function getStatuses(): array
    {
        return WatchingStatus::cases();
    }

    public function render()
    {
        return view('livewire.movies.movie-show', [
            'statuses' => $this->getStatuses(),
        ])->layout('layouts.app');
    }
}
