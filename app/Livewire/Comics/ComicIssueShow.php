<?php

declare(strict_types=1);

namespace App\Livewire\Comics;

use App\Enums\ReadingStatus;
use App\Models\Comic;
use App\Models\ComicIssue;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ComicIssueShow extends Component
{
    use AuthorizesRequests;

    public Comic $comic;
    public ComicIssue $issue;

    public function mount(Comic $comic, ComicIssue $issue): void
    {
        $this->authorize('view', $comic);
        
        if ($issue->comic_id !== $comic->id) {
            abort(404);
        }

        $this->comic = $comic;
        $this->issue = $issue;
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->comic);

        $updates = ['status' => $status];

        if ($status === 'read' && ! $this->issue->date_read) {
            $updates['date_read'] = now();
        }

        $this->issue->update($updates);
        $this->issue->refresh();
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->comic);

        $this->issue->update(['rating' => $this->issue->rating === $rating ? null : $rating]);
        $this->issue->refresh();
    }

    public function saveNotes(string $notes): void
    {
        $this->authorize('update', $this->comic);
        $this->issue->update(['notes' => $notes]);
        session()->flash('message', 'Notes saved.');
    }

    public function render()
    {
        return view('livewire.comics.comic-issue-show', [
            'statuses' => ReadingStatus::cases(),
        ])->layout('layouts.app');
    }
}
