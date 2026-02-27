<?php

declare(strict_types=1);

namespace App\Livewire\Comics;

use App\Enums\ReadingStatus;
use App\Models\Comic;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ComicForm extends Component
{
    use AuthorizesRequests;

    public ?Comic $comic = null;

    public string $title = '';

    public string $publisher = '';

    public ?int $start_year = null;

    public ?int $issue_count = null;

    public string $description = '';

    public string $cover_url = '';

    public string $comicvine_volume_id = '';

    public string $comicvine_url = '';

    public string $creators = '';

    public string $characters = '';

    public string $status = 'want_to_read';

    public ?int $rating = null;

    public ?string $date_started = null;

    public ?string $date_finished = null;

    public string $notes = '';

    public string $review = '';

    public function mount(?Comic $comic = null): void
    {
        if ($comic && $comic->exists) {
            $this->authorize('update', $comic);
            $this->comic = $comic;
            $this->fill([
                'title' => $comic->title,
                'publisher' => $comic->publisher ?? '',
                'start_year' => $comic->start_year,
                'issue_count' => $comic->issue_count,
                'description' => $comic->description ?? '',
                'cover_url' => $comic->cover_url ?? '',
                'comicvine_volume_id' => $comic->comicvine_volume_id ?? '',
                'comicvine_url' => $comic->comicvine_url ?? '',
                'creators' => $comic->creators ?? '',
                'characters' => $comic->characters ?? '',
                'status' => $comic->status->value,
                'rating' => $comic->rating,
                'date_started' => $comic->date_started?->format('d/m/Y'),
                'date_finished' => $comic->date_finished?->format('d/m/Y'),
                'notes' => $comic->notes ?? '',
                'review' => $comic->review ?? '',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'start_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'issue_count' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:10000'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'comicvine_volume_id' => ['nullable', 'string', 'max:255'],
            'comicvine_url' => ['nullable', 'url', 'max:2048'],
            'creators' => ['nullable', 'string', 'max:2000'],
            'characters' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(ReadingStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'date_started' => ['nullable', 'date_format:d/m/Y'],
            'date_finished' => ['nullable', 'date_format:d/m/Y'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'review' => ['nullable', 'string', 'max:10000'],
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

        if ($validated['date_started'] && $validated['date_finished']) {
            if ($validated['date_finished'] < $validated['date_started']) {
                $this->addError('date_finished', 'Date finished must be after or equal to date started.');
                return;
            }
        }

        $data = [
            'title' => $validated['title'],
            'publisher' => $validated['publisher'] ?: null,
            'start_year' => $validated['start_year'],
            'issue_count' => $validated['issue_count'],
            'description' => $validated['description'] ?: null,
            'cover_url' => $validated['cover_url'] ?: null,
            'comicvine_volume_id' => $validated['comicvine_volume_id'] ?: null,
            'comicvine_url' => $validated['comicvine_url'] ?: null,
            'creators' => $validated['creators'] ?: null,
            'characters' => $validated['characters'] ?: null,
            'status' => $validated['status'],
            'rating' => $validated['rating'],
            'date_started' => $validated['date_started'],
            'date_finished' => $validated['date_finished'],
            'notes' => $validated['notes'] ?: null,
            'review' => $validated['review'] ?: null,
        ];

        if ($this->comic) {
            $this->comic->update($data);
            $message = 'Comic updated successfully.';
        } else {
            $data['user_id'] = Auth::id();
            $data['date_added'] = now();
            $this->comic = Comic::create($data);
            $message = 'Comic created successfully.';
        }

        session()->flash('message', $message);

        $this->redirect(route('comics.show', $this->comic));
    }

    public function isEditing(): bool
    {
        return $this->comic !== null && $this->comic->exists;
    }

    public function render()
    {
        return view('livewire.comics.comic-form', [
            'statuses' => ReadingStatus::cases(),
            'isEditing' => $this->isEditing(),
        ])->layout('layouts.app');
    }
}
