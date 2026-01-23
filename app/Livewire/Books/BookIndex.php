<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Enums\ReadingStatus;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BookIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortBy' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updateStatus(Book $book, string $status): void
    {
        $this->authorize('update', $book);

        $book->update([
            'status' => $status,
            'date_started' => $status === 'reading' && !$book->date_started ? now() : $book->date_started,
            'date_finished' => $status === 'read' && !$book->date_finished ? now() : $book->date_finished,
        ]);
    }

    public function deleteBook(Book $book): void
    {
        $this->authorize('delete', $book);

        $book->delete();

        session()->flash('message', 'Book deleted successfully.');
    }

    public function getStatuses(): array
    {
        return ReadingStatus::cases();
    }

    public function render()
    {
        $books = Book::query()
            ->where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('author', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(12);

        return view('livewire.books.book-index', [
            'books' => $books,
            'statuses' => $this->getStatuses(),
        ])->layout('layouts.app');
    }
}
