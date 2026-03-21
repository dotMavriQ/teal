<?php

declare(strict_types=1);

namespace App\Livewire\Games;

use App\Models\Game;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class GameShow extends Component
{
    use AuthorizesRequests;

    public Game $game;

    public function mount(Game $game): void
    {
        $this->authorize('view', $game);
        $this->game = $game;
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->game);

        $newRating = $this->game->rating === $rating ? null : $rating;
        $this->game->update(['rating' => $newRating]);
    }

    public function deleteGame(): void
    {
        $this->authorize('delete', $this->game);

        $this->game->delete();

        session()->flash('message', 'Game deleted successfully.');
        $this->redirect(route('games.index'));
    }

    public function render()
    {
        return view('livewire.games.game-show')
            ->layout('layouts.app');
    }
}
