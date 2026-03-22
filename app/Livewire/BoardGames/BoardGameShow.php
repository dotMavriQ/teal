<?php

declare(strict_types=1);

namespace App\Livewire\BoardGames;

use App\Models\BoardGame;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class BoardGameShow extends Component
{
    use AuthorizesRequests;

    public BoardGame $boardGame;

    public function mount(BoardGame $boardGame): void
    {
        $this->authorize('view', $boardGame);
        $this->boardGame = $boardGame;
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->boardGame);

        $newRating = $this->boardGame->rating === $rating ? null : $rating;
        $this->boardGame->update(['rating' => $newRating]);
    }

    public function deleteBoardGame(): void
    {
        $this->authorize('delete', $this->boardGame);

        $this->boardGame->delete();

        session()->flash('message', 'Board game deleted successfully.');
        $this->redirect(route('board-games.index'));
    }

    public function render()
    {
        return view('livewire.board-games.board-game-show')
            ->layout('layouts.app');
    }
}
