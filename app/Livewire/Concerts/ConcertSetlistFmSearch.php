<?php

declare(strict_types=1);

namespace App\Livewire\Concerts;

use App\Enums\ListeningStatus;
use App\Models\Concert;
use App\Services\SetlistFmService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ConcertSetlistFmSearch extends Component
{
    public string $step = 'search';

    public string $searchQuery = '';

    public array $artists = [];

    public ?string $selectedArtistMbid = null;

    public ?string $selectedArtistName = null;

    public array $setlists = [];

    public ?array $selectedSetlist = null;

    public string $status = 'attended';

    public ?int $rating = null;

    public string $notes = '';

    public function searchArtists(): void
    {
        if (empty(trim($this->searchQuery))) {
            return;
        }

        $service = app(SetlistFmService::class);
        $this->artists = $service->searchArtists($this->searchQuery);
        $this->step = 'artists';
    }

    public function selectArtist(string $mbid, string $name): void
    {
        $this->selectedArtistMbid = $mbid;
        $this->selectedArtistName = $name;

        $service = app(SetlistFmService::class);
        $result = $service->getArtistSetlists($mbid);
        $this->setlists = $result['setlists'];
        $this->step = 'setlists';
    }

    public function selectSetlist(int $index): void
    {
        $this->selectedSetlist = $this->setlists[$index] ?? null;

        if ($this->selectedSetlist) {
            $this->step = 'configure';
        }
    }

    public function save(): void
    {
        if (! $this->selectedSetlist) {
            return;
        }

        $existing = Concert::where('user_id', Auth::id())
            ->where('setlist_fm_id', $this->selectedSetlist['setlist_fm_id'])
            ->exists();

        if ($existing) {
            session()->flash('error', 'This concert is already in your library.');

            return;
        }

        $concert = Concert::create([
            'user_id' => Auth::id(),
            'artist' => $this->selectedSetlist['artist'],
            'artist_mbid' => $this->selectedSetlist['artist_mbid'],
            'tour_name' => $this->selectedSetlist['tour_name'],
            'venue' => $this->selectedSetlist['venue'],
            'city' => $this->selectedSetlist['city'],
            'country' => $this->selectedSetlist['country'],
            'event_date' => $this->selectedSetlist['event_date'],
            'setlist' => $this->selectedSetlist['setlist'],
            'setlist_fm_id' => $this->selectedSetlist['setlist_fm_id'],
            'status' => $this->status,
            'rating' => $this->rating,
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('message', "{$concert->artist} concert added successfully.");

        $this->redirect(route('concerts.show', $concert));
    }

    public function back(): void
    {
        $this->step = match ($this->step) {
            'configure' => 'setlists',
            'setlists' => 'artists',
            'artists' => 'search',
            default => 'search',
        };
    }

    public function render()
    {
        return view('livewire.concerts.concert-setlistfm-search', [
            'statuses' => ListeningStatus::cases(),
        ])->layout('layouts.app');
    }
}
