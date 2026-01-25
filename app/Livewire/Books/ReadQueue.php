<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Enums\ReadingStatus;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReadQueue extends Component
{
    public function addToQueue(Book $book): void
    {
        $this->authorize('update', $book);

        if ($book->queue_position !== null) {
            return; // Already in queue
        }

        // Get the next position
        $maxPosition = Book::where('user_id', Auth::id())
            ->whereNotNull('queue_position')
            ->max('queue_position') ?? 0;

        $book->update(['queue_position' => $maxPosition + 1]);
    }

    public function removeFromQueue(Book $book): void
    {
        $this->authorize('update', $book);

        $oldPosition = $book->queue_position;
        $book->update(['queue_position' => null]);

        // Reorder remaining items
        if ($oldPosition !== null) {
            Book::where('user_id', Auth::id())
                ->where('queue_position', '>', $oldPosition)
                ->decrement('queue_position');
        }
    }

    public function moveUp(Book $book): void
    {
        $this->authorize('update', $book);

        if ($book->queue_position === null || $book->queue_position <= 1) {
            return;
        }

        $swapWith = Book::where('user_id', Auth::id())
            ->where('queue_position', $book->queue_position - 1)
            ->first();

        if ($swapWith) {
            $swapWith->update(['queue_position' => $book->queue_position]);
            $book->update(['queue_position' => $book->queue_position - 1]);
        }
    }

    public function moveDown(Book $book): void
    {
        $this->authorize('update', $book);

        if ($book->queue_position === null) {
            return;
        }

        $swapWith = Book::where('user_id', Auth::id())
            ->where('queue_position', $book->queue_position + 1)
            ->first();

        if ($swapWith) {
            $swapWith->update(['queue_position' => $book->queue_position]);
            $book->update(['queue_position' => $book->queue_position + 1]);
        }
    }

    public function moveToTop(Book $book): void
    {
        $this->authorize('update', $book);

        if ($book->queue_position === null || $book->queue_position === 1) {
            return;
        }

        // Shift all items above down by 1
        Book::where('user_id', Auth::id())
            ->where('queue_position', '<', $book->queue_position)
            ->increment('queue_position');

        $book->update(['queue_position' => 1]);
    }

    public function moveToBottom(Book $book): void
    {
        $this->authorize('update', $book);

        if ($book->queue_position === null) {
            return;
        }

        $maxPosition = Book::where('user_id', Auth::id())
            ->whereNotNull('queue_position')
            ->max('queue_position');

        if ($book->queue_position === $maxPosition) {
            return;
        }

        // Shift all items below up by 1
        Book::where('user_id', Auth::id())
            ->where('queue_position', '>', $book->queue_position)
            ->decrement('queue_position');

        $book->update(['queue_position' => $maxPosition]);
    }

    public function updateStatus(Book $book, string $status): void
    {
        $this->authorize('update', $book);

        $book->update([
            'status' => $status,
            'date_started' => $status === 'reading' && !$book->date_started ? now() : $book->date_started,
        ]);

        // Auto-remove from queue when marked as read
        if ($status === 'read' && $book->queue_position !== null) {
            $this->removeFromQueue($book);
        }
    }

    public function render()
    {
        $queuedBooks = Book::where('user_id', Auth::id())
            ->whereNotNull('queue_position')
            ->where('status', '!=', ReadingStatus::Read)
            ->orderBy('queue_position')
            ->get();

        return view('livewire.books.read-queue', [
            'books' => $queuedBooks,
            'statuses' => ReadingStatus::cases(),
        ])->layout('layouts.app');
    }
}
