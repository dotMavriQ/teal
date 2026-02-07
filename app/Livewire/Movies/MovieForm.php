<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Enums\WatchingStatus;
use App\Models\Movie;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MovieForm extends Component
{
    use AuthorizesRequests;

    public ?Movie $movie = null;

    public string $title = '';

    public string $original_title = '';

    public string $director = '';

    public ?int $year = null;

    public ?int $runtime_minutes = null;

    public string $genres = '';

    public string $status = 'watchlist';

    public ?int $rating = null;

    public string $poster_url = '';

    public string $imdb_id = '';

    public ?string $release_date = null;

    public ?string $date_watched = null;

    public string $description = '';

    public string $notes = '';

    public function mount(?Movie $movie = null): void
    {
        if ($movie && $movie->exists) {
            $this->authorize('update', $movie);
            $this->movie = $movie;
            $this->fill([
                'title' => $movie->title,
                'original_title' => $movie->original_title ?? '',
                'director' => $movie->director ?? '',
                'year' => $movie->year,
                'runtime_minutes' => $movie->runtime_minutes,
                'genres' => $movie->genres ?? '',
                'status' => $movie->status->value,
                'rating' => $movie->rating,
                'poster_url' => $movie->poster_url ?? '',
                'imdb_id' => $movie->imdb_id ?? '',
                'release_date' => $movie->release_date?->format('d/m/Y'),
                'date_watched' => $movie->date_watched?->format('d/m/Y'),
                'description' => $movie->description ?? '',
                'notes' => $movie->notes ?? '',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'director' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1888', 'max:2100'],
            'runtime_minutes' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'genres' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::enum(WatchingStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'poster_url' => ['nullable', 'url', 'max:2048'],
            'imdb_id' => ['nullable', 'string', 'max:20'],
            'release_date' => ['nullable', 'date_format:d/m/Y'],
            'date_watched' => ['nullable', 'date_format:d/m/Y'],
            'description' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    protected function parseDateInput(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];

            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        return null;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['release_date'] = $this->parseDateInput($validated['release_date'] ?? null);
        $validated['date_watched'] = $this->parseDateInput($validated['date_watched'] ?? null);

        $data = [
            'title' => $validated['title'],
            'original_title' => $validated['original_title'] ?: null,
            'director' => $validated['director'] ?: null,
            'year' => $validated['year'],
            'runtime_minutes' => $validated['runtime_minutes'],
            'genres' => $validated['genres'] ?: null,
            'status' => $validated['status'],
            'rating' => $validated['rating'],
            'poster_url' => $validated['poster_url'] ?: null,
            'imdb_id' => $validated['imdb_id'] ?: null,
            'release_date' => $validated['release_date'],
            'date_watched' => $validated['date_watched'],
            'description' => $validated['description'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->movie) {
            $this->movie->update($data);
            $message = 'Movie updated successfully.';
        } else {
            $data['user_id'] = Auth::id();
            $data['date_added'] = now();
            $this->movie = Movie::create($data);
            $message = 'Movie created successfully.';
        }

        session()->flash('message', $message);

        $this->redirect(route('movies.show', $this->movie));
    }

    public function getStatuses(): array
    {
        return WatchingStatus::cases();
    }

    public function isEditing(): bool
    {
        return $this->movie !== null && $this->movie->exists;
    }

    public function render()
    {
        return view('livewire.movies.movie-form', [
            'statuses' => $this->getStatuses(),
            'isEditing' => $this->isEditing(),
        ])->layout('layouts.app');
    }
}
