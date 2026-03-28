<?php

declare(strict_types=1);

namespace App\Livewire\Concerts;

use App\Enums\ListeningStatus;
use App\Models\Concert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ConcertForm extends Component
{
    use AuthorizesRequests;

    public ?Concert $concert = null;

    public string $artist = '';

    public string $tour_name = '';

    public string $venue = '';

    public string $city = '';

    public string $country = '';

    public ?string $event_date = null;

    public string $cover_url = '';

    public string $status = 'attended';

    public ?int $rating = null;

    public ?string $setlist_fm_id = null;

    public ?string $artist_mbid = null;

    public string $notes = '';

    public function mount(?Concert $concert = null): void
    {
        if ($concert && $concert->exists) {
            $this->authorize('update', $concert);
            $this->concert = $concert;
            $this->fill([
                'artist' => $concert->artist,
                'tour_name' => $concert->tour_name ?? '',
                'venue' => $concert->venue ?? '',
                'city' => $concert->city ?? '',
                'country' => $concert->country ?? '',
                'event_date' => $concert->event_date?->format('d/m/Y'),
                'cover_url' => $concert->cover_url ?? '',
                'status' => $concert->status->value,
                'rating' => $concert->rating,
                'setlist_fm_id' => $concert->setlist_fm_id,
                'artist_mbid' => $concert->artist_mbid,
                'notes' => $concert->notes ?? '',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'artist' => ['required', 'string', 'max:255'],
            'tour_name' => ['nullable', 'string', 'max:255'],
            'venue' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date_format:d/m/Y'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::enum(ListeningStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'setlist_fm_id' => ['nullable', 'string', 'max:255'],
            'artist_mbid' => ['nullable', 'string', 'max:255'],
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
            return checkdate((int) $matches[2], (int) $matches[1], (int) $matches[3])
                ? "{$matches[3]}-{$matches[2]}-{$matches[1]}"
                : null;
        }

        return null;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['event_date'] = $this->parseDateInput($validated['event_date'] ?? null);

        $data = [
            'artist' => $validated['artist'],
            'tour_name' => $validated['tour_name'] ?: null,
            'venue' => $validated['venue'] ?: null,
            'city' => $validated['city'] ?: null,
            'country' => $validated['country'] ?: null,
            'event_date' => $validated['event_date'],
            'cover_url' => $validated['cover_url'] ?: null,
            'status' => $validated['status'],
            'rating' => $validated['rating'],
            'setlist_fm_id' => $validated['setlist_fm_id'] ?: null,
            'artist_mbid' => $validated['artist_mbid'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->concert) {
            $this->concert->update($data);
            $message = 'Concert updated successfully.';
        } else {
            $data['user_id'] = Auth::id();
            $this->concert = Concert::create($data);
            $message = 'Concert created successfully.';
        }

        session()->flash('message', $message);

        $this->redirect(route('concerts.show', $this->concert));
    }

    public function isEditing(): bool
    {
        return $this->concert !== null && $this->concert->exists;
    }

    public function render()
    {
        return view('livewire.concerts.concert-form', [
            'statuses' => ListeningStatus::cases(),
            'isEditing' => $this->isEditing(),
        ])->layout('layouts.app');
    }
}
