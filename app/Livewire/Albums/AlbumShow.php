<?php

declare(strict_types=1);

namespace App\Livewire\Albums;

use App\Models\Album;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class AlbumShow extends Component
{
    use AuthorizesRequests;

    public Album $album;

    public function mount(Album $album): void
    {
        $this->authorize('view', $album);
        $this->album = $album;
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->album);

        $newRating = $this->album->rating === $rating ? null : $rating;
        $this->album->update(['rating' => $newRating]);
    }

    public function deleteAlbum(): void
    {
        $this->authorize('delete', $this->album);

        $this->album->delete();

        session()->flash('message', 'Album deleted successfully.');
        $this->redirect(route('albums.index'));
    }

    public function render()
    {
        return view('livewire.albums.album-show')
            ->layout('layouts.app');
    }
}
