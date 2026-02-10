<?php

declare(strict_types=1);

namespace App\Livewire\Anime;

use App\Enums\WatchingStatus;
use App\Models\Anime;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AnimeForm extends Component
{
    use AuthorizesRequests;

    public ?Anime $anime = null;

    public string $title = '';

    public string $original_title = '';

    public ?int $year = null;

    public ?int $episodes_total = null;

    public ?int $episodes_watched = null;

    public ?int $runtime_minutes = null;

    public string $genres = '';

    public string $studios = '';

    public string $media_type = '';

    public string $status = 'watchlist';

    public ?int $rating = null;

    public string $poster_url = '';

    public ?int $mal_id = null;

    public ?string $date_started = null;

    public ?string $date_finished = null;

    public string $description = '';

    public string $notes = '';

    public function mount(?Anime $anime = null): void
    {
        if ($anime && $anime->exists) {
            $this->authorize('update', $anime);
            $this->anime = $anime;
            $this->fill([
                'title' => $anime->title,
                'original_title' => $anime->original_title ?? '',
                'year' => $anime->year,
                'episodes_total' => $anime->episodes_total,
                'episodes_watched' => $anime->episodes_watched,
                'runtime_minutes' => $anime->runtime_minutes,
                'genres' => $anime->genres ?? '',
                'studios' => $anime->studios ?? '',
                'media_type' => $anime->media_type ?? '',
                'status' => $anime->status->value,
                'rating' => $anime->rating,
                'poster_url' => $anime->poster_url ?? '',
                'mal_id' => $anime->mal_id,
                'date_started' => $anime->date_started?->format('d/m/Y'),
                'date_finished' => $anime->date_finished?->format('d/m/Y'),
                'description' => $anime->description ?? '',
                'notes' => $anime->notes ?? '',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'episodes_total' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'episodes_watched' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'runtime_minutes' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'genres' => ['nullable', 'string', 'max:500'],
            'studios' => ['nullable', 'string', 'max:500'],
            'media_type' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::enum(WatchingStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'poster_url' => ['nullable', 'url', 'max:2048'],
            'mal_id' => ['nullable', 'integer'],
            'date_started' => ['nullable', 'date_format:d/m/Y'],
            'date_finished' => ['nullable', 'date_format:d/m/Y'],
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

        $validated['date_started'] = $this->parseDateInput($validated['date_started'] ?? null);
        $validated['date_finished'] = $this->parseDateInput($validated['date_finished'] ?? null);

        $data = [
            'title' => $validated['title'],
            'original_title' => $validated['original_title'] ?: null,
            'year' => $validated['year'],
            'episodes_total' => $validated['episodes_total'],
            'episodes_watched' => $validated['episodes_watched'],
            'runtime_minutes' => $validated['runtime_minutes'],
            'genres' => $validated['genres'] ?: null,
            'studios' => $validated['studios'] ?: null,
            'media_type' => $validated['media_type'] ?: null,
            'status' => $validated['status'],
            'rating' => $validated['rating'],
            'poster_url' => $validated['poster_url'] ?: null,
            'mal_id' => $validated['mal_id'],
            'date_started' => $validated['date_started'],
            'date_finished' => $validated['date_finished'],
            'description' => $validated['description'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->anime) {
            $this->anime->update($data);
            $message = 'Anime updated successfully.';
        } else {
            $data['user_id'] = Auth::id();
            $data['date_added'] = now();
            $this->anime = Anime::create($data);
            $message = 'Anime created successfully.';
        }

        session()->flash('message', $message);

        $this->redirect(route('anime.show', $this->anime));
    }

    public function getStatuses(): array
    {
        return WatchingStatus::cases();
    }

    public function isEditing(): bool
    {
        return $this->anime !== null && $this->anime->exists;
    }

    public function render()
    {
        return view('livewire.anime.anime-form', [
            'statuses' => $this->getStatuses(),
            'isEditing' => $this->isEditing(),
        ])->layout('layouts.app');
    }
}
