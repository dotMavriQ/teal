<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Enums\ReadingStatus;
use App\Models\Book;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookShow extends Component
{
    use AuthorizesRequests;

    public Book $book;

    public function mount(Book $book): void
    {
        $this->authorize('view', $book);
        $this->book = $book;
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->book);

        $updates = ['status' => $status];

        if ($status === 'reading' && ! $this->book->date_started) {
            $updates['date_started'] = now();
        }

        if ($status === 'read' && ! $this->book->date_finished) {
            $updates['date_finished'] = now();
        }

        $this->book->update($updates);

        // Auto-remove from queue when marked as read
        if ($status === 'read' && $this->book->queue_position !== null) {
            $this->removeFromQueue();
        }

        $this->book->refresh();
    }

    public function addToQueue(): void
    {
        $this->authorize('update', $this->book);

        if ($this->book->queue_position !== null) {
            return;
        }

        $maxPosition = Book::where('user_id', Auth::id())
            ->whereNotNull('queue_position')
            ->max('queue_position') ?? 0;

        $this->book->update(['queue_position' => $maxPosition + 1]);
        $this->book->refresh();
    }

    public function removeFromQueue(): void
    {
        $this->authorize('update', $this->book);

        $oldPosition = $this->book->queue_position;
        $this->book->update(['queue_position' => null]);

        if ($oldPosition !== null) {
            Book::where('user_id', Auth::id())
                ->where('queue_position', '>', $oldPosition)
                ->decrement('queue_position');
        }

        $this->book->refresh();
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->book);

        $this->book->update(['rating' => $rating]);
        $this->book->refresh();
    }

    public function deleteBook(): void
    {
        $this->authorize('delete', $this->book);

        $this->book->delete();

        session()->flash('message', 'Book deleted successfully.');

        $this->redirect(route('books.index'));
    }

    public function getStatuses(): array
    {
        return ReadingStatus::cases();
    }

    public function render()
    {
        return view('livewire.books.book-show', [
            'statuses' => $this->getStatuses(),
        ])->layout('layouts.app');
    }
}
