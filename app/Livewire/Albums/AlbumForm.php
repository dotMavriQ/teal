<?php

declare(strict_types=1);

namespace App\Livewire\Albums;

use App\Enums\CollectionStatus;
use App\Enums\OwnershipStatus;
use App\Models\Album;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AlbumForm extends Component
{
    use AuthorizesRequests;

    public ?Album $album = null;

    public string $title = '';

    public string $artist = '';

    public string $genre = '';

    public string $styles = '';

    public ?int $year = null;

    public string $format = '';

    public string $label = '';

    public string $country = '';

    public string $cover_url = '';

    public string $status = 'wishlist';

    public string $ownership = 'not_owned';

    public ?int $rating = null;

    public ?int $discogs_id = null;

    public ?int $discogs_master_id = null;

    public string $notes = '';

    public function mount(?Album $album = null): void
    {
        if ($album && $album->exists) {
            $this->authorize('update', $album);
            $this->album = $album;
            $this->fill([
                'title' => $album->title,
                'artist' => $album->artist ?? '',
                'genre' => is_array($album->genre) ? implode(', ', $album->genre) : '',
                'styles' => is_array($album->styles) ? implode(', ', $album->styles) : '',
                'year' => $album->year,
                'format' => $album->format ?? '',
                'label' => $album->label ?? '',
                'country' => $album->country ?? '',
                'cover_url' => $album->cover_url ?? '',
                'status' => $album->status->value,
                'ownership' => $album->ownership?->value ?? 'not_owned',
                'rating' => $album->rating,
                'discogs_id' => $album->discogs_id,
                'discogs_master_id' => $album->discogs_master_id,
                'notes' => $album->notes ?? '',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:500'],
            'styles' => ['nullable', 'string', 'max:500'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'format' => ['nullable', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::enum(CollectionStatus::class)],
            'ownership' => ['required', Rule::enum(OwnershipStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'discogs_id' => ['nullable', 'integer'],
            'discogs_master_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $genreArray = ! empty($validated['genre'])
            ? array_map('trim', explode(',', $validated['genre']))
            : [];

        $stylesArray = ! empty($validated['styles'])
            ? array_map('trim', explode(',', $validated['styles']))
            : [];

        $data = [
            'title' => $validated['title'],
            'artist' => $validated['artist'] ?: null,
            'genre' => $genreArray,
            'styles' => $stylesArray,
            'year' => $validated['year'],
            'format' => $validated['format'] ?: null,
            'label' => $validated['label'] ?: null,
            'country' => $validated['country'] ?: null,
            'cover_url' => $validated['cover_url'] ?: null,
            'status' => $validated['status'],
            'ownership' => $validated['ownership'],
            'rating' => $validated['rating'],
            'discogs_id' => $validated['discogs_id'],
            'discogs_master_id' => $validated['discogs_master_id'],
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->album) {
            $this->album->update($data);
            $message = 'Album updated successfully.';
        } else {
            $data['user_id'] = Auth::id();
            $this->album = Album::create($data);
            $message = 'Album created successfully.';
        }

        session()->flash('message', $message);

        $this->redirect(route('albums.show', $this->album));
    }

    public function isEditing(): bool
    {
        return $this->album !== null && $this->album->exists;
    }

    public function render()
    {
        return view('livewire.albums.album-form', [
            'statuses' => CollectionStatus::cases(),
            'ownershipStatuses' => OwnershipStatus::cases(),
            'isEditing' => $this->isEditing(),
        ])->layout('layouts.app');
    }
}
