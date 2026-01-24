<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Enums\ReadingStatus;
use App\Models\Book;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
