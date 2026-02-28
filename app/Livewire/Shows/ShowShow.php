<?php

declare(strict_types=1);

namespace App\Livewire\Shows;

use App\Enums\WatchingStatus;
use App\Models\Show;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ShowShow extends Component
{
    use AuthorizesRequests;

    public Show $show;
    public string $posterUrlInput = '';
    public bool $showPosterForm = false;

    public function mount(Show $show): void
    {
        $this->authorize('view', $show);
        $this->show = $show;
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->show);
        $this->show->update(['status' => $status]);
        $this->show->refresh();
        session()->flash('message', 'Status updated.');
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->show);
        $newRating = $this->show->rating === $rating ? null : $rating;
        $this->show->update(['rating' => $newRating]);
        $this->show->refresh();
    }

    public function deleteShow(): void
    {
        $this->authorize('delete', $this->show);
        $this->show->delete();
        session()->flash('message', 'Show deleted.');
        $this->redirect(route('shows.index'), navigate: true);
    }

    public function savePosterUrl(): void
    {
        $this->authorize('update', $this->show);
        $this->validate(['posterUrlInput' => ['nullable', 'url', 'max:2048']]);
        $this->show->update(['poster_url' => $this->posterUrlInput ?: null]);
        $this->show->refresh();
        $this->showPosterForm = false;
        session()->flash('message', 'Poster updated.');
    }

    public function togglePosterForm(): void
    {
        $this->showPosterForm = !$this->showPosterForm;
        $this->posterUrlInput = $this->show->poster_url ?? '';
    }

    public function getStatuses(): array
    {
        return WatchingStatus::cases();
    }

    public function render()
    {
        return view('livewire.shows.show-show', [
            'statuses' => $this->getStatuses(),
        ])->layout('layouts.app');
    }
}
