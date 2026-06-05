<?php

declare(strict_types=1);

namespace App\Livewire\Concerts;

use App\Models\Concert;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ConcertShow extends Component
{
    use AuthorizesRequests;

    public Concert $concert;

    public function mount(Concert $concert): void
    {
        $this->authorize('view', $concert);
        $this->concert = $concert;
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->concert);

        $newRating = $this->concert->rating === $rating ? null : $rating;
        $this->concert->update(['rating' => $newRating]);
    }

    public function deleteConcert(): void
    {
        $this->authorize('delete', $this->concert);

        $this->concert->delete();

        session()->flash('message', 'Concert deleted successfully.');
        $this->redirect(route('concerts.index'));
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.concerts.concert-show');
    }
}
