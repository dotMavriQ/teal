<?php

declare(strict_types=1);

namespace App\Livewire\BoardGames;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use App\Models\BoardGame;
use App\Services\BggService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BoardGameBggSearch extends Component
{
    public string $step = 'search';

    public string $searchQuery = '';

    public array $results = [];

    public ?array $selectedGame = null;

    public string $status = 'backlog';

    public string $ownership = 'owned';

    public ?int $rating = null;

    public string $notes = '';

    public function search(): void
    {
        if (trim($this->searchQuery) === '') {
            return;
        }

        $bgg = app(BggService::class);
        $this->results = $bgg->search($this->searchQuery);
        $this->step = 'results';
    }

    public function selectGame(int $bggId): void
    {
        $bgg = app(BggService::class);
        $details = $bgg->getDetails($bggId);

        if (! $details) {
            session()->flash('error', 'Could not fetch board game details.');
            return;
        }

        $this->selectedGame = $details;
        $this->step = 'configure';
    }

    public function save(): void
    {
        if (! $this->selectedGame) {
            return;
        }

        $boardGame = BoardGame::create([
            'user_id' => Auth::id(),
            'title' => $this->selectedGame['title'],
            'genre' => $this->selectedGame['genres'] ?? [],
            'description' => $this->selectedGame['description'] ?? null,
            'cover_url' => $this->selectedGame['cover_url'] ?? null,
            'year_published' => $this->selectedGame['year_published'] ?? null,
            'designer' => $this->selectedGame['designer'] ?? null,
            'publisher' => $this->selectedGame['publisher'] ?? null,
            'min_players' => $this->selectedGame['min_players'] ?? null,
            'max_players' => $this->selectedGame['max_players'] ?? null,
            'playing_time' => $this->selectedGame['playing_time'] ?? null,
            'status' => $this->status,
            'ownership' => $this->ownership,
            'rating' => $this->rating,
            'bgg_id' => $this->selectedGame['bgg_id'],
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('message', "{$boardGame->title} added to your collection!");
        $this->redirect(route('board-games.show', $boardGame));
    }

    public function backToResults(): void
    {
        $this->selectedGame = null;
        $this->step = 'results';
    }

    public function backToSearch(): void
    {
        $this->results = [];
        $this->step = 'search';
    }

    public function render()
    {
        return view('livewire.board-games.board-game-bgg-search', [
            'statuses' => PlayingStatus::cases(),
            'ownershipStatuses' => OwnershipStatus::cases(),
        ])->layout('layouts.app');
    }
}
