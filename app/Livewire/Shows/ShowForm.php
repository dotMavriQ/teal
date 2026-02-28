<?php

declare(strict_types=1);

namespace App\Livewire\Shows;

use App\Enums\WatchingStatus;
use App\Models\Show;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ShowForm extends Component
{
    use AuthorizesRequests;

    public ?Show $show = null;

    public string $title = '';
    public string $original_title = '';
    public ?int $year = null;
    public string $genres = '';
    public string $status = 'watchlist';
    public ?int $rating = null;
    public string $poster_url = '';
    public string $imdb_id = '';
    public ?string $release_date = null;
    public ?string $date_added = null;
    public string $description = '';
    public string $notes = '';

    public function mount(?Show $show = null): void
    {
        if ($show && $show->exists) {
            $this->authorize('update', $show);
            $this->show = $show;
            $this->title = $show->title ?? '';
            $this->original_title = $show->original_title ?? '';
            $this->year = $show->year;
            $this->genres = $show->genres ?? '';
            $this->status = $show->status->value;
            $this->rating = $show->rating;
            $this->poster_url = $show->poster_url ?? '';
            $this->imdb_id = $show->imdb_id ?? '';
            $this->release_date = $show->release_date?->format('d/m/Y');
            $this->date_added = $show->date_added?->format('d/m/Y');
            $this->description = $show->description ?? '';
            $this->notes = $show->notes ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'genres' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::enum(WatchingStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'poster_url' => ['nullable', 'url', 'max:2048'],
            'imdb_id' => ['nullable', 'string', 'max:20'],
            'release_date' => ['nullable', 'date_format:d/m/Y'],
            'date_added' => ['nullable', 'date_format:d/m/Y'],
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
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }

        return null;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = [
            'title' => $validated['title'],
            'original_title' => $validated['original_title'] ?: null,
            'year' => $validated['year'],
            'genres' => $validated['genres'] ?: null,
            'status' => $validated['status'],
            'rating' => $validated['rating'],
            'poster_url' => $validated['poster_url'] ?: null,
            'imdb_id' => $validated['imdb_id'] ?: null,
            'release_date' => $this->parseDateInput($validated['release_date']),
            'date_added' => $this->parseDateInput($validated['date_added']),
            'description' => $validated['description'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->show && $this->show->exists) {
            $this->show->update($data);
            $show = $this->show;
            session()->flash('message', 'Show updated successfully.');
        } else {
            $data['user_id'] = Auth::id();
            $data['date_added'] = $data['date_added'] ?? now()->toDateString();
            $show = Show::create($data);
            session()->flash('message', 'Show added successfully.');
        }

        $this->redirect(route('shows.show', $show), navigate: true);
    }

    public function getStatuses(): array
    {
        return WatchingStatus::cases();
    }

    public function isEditing(): bool
    {
        return $this->show !== null && $this->show->exists;
    }

    public function render()
    {
        return view('livewire.shows.show-form', [
            'statuses' => $this->getStatuses(),
            'isEditing' => $this->isEditing(),
        ])->layout('layouts.app');
    }
}
