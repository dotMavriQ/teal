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

    // Bulk delete
    public array $selected = [];
    public bool $selectAll = false;

    // Delete all modal
    public bool $showDeleteAllModal = false;
    public string $confirmationWord = '';
    public string $confirmationInput = '';

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

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected = Book::query()
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
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function deleteSelected(): void
    {
        $count = Book::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $this->selected)
            ->delete();

        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', "{$count} book(s) deleted successfully.");
    }

    public function openDeleteAllModal(): void
    {
        $this->confirmationWord = $this->generateConfirmationWord();
        $this->confirmationInput = '';
        $this->showDeleteAllModal = true;
    }

    public function closeDeleteAllModal(): void
    {
        $this->showDeleteAllModal = false;
        $this->confirmationInput = '';
    }

    public function deleteAllBooks(): void
    {
        if ($this->confirmationInput !== $this->confirmationWord) {
            $this->addError('confirmationInput', 'Confirmation word does not match.');
            return;
        }

        $count = Book::query()
            ->where('user_id', Auth::id())
            ->delete();

        $this->selected = [];
        $this->selectAll = false;
        $this->showDeleteAllModal = false;
        $this->confirmationInput = '';

        session()->flash('message', "All {$count} book(s) have been permanently deleted.");
    }

    protected function generateConfirmationWord(): string
    {
        $words = [
            'obliterate', 'permanent', 'irreversible', 'destruction', 'annihilate',
            'eradicate', 'demolition', 'exterminate', 'catastrophe', 'apocalypse',
            'decimation', 'liquidate', 'elimination', 'termination', 'expunction',
        ];

        $word = $words[array_rand($words)];
        $chars = str_split($word);
        $length = count($chars);

        // Pick two random distinct positions to capitalize
        $pos1 = random_int(0, $length - 1);
        do {
            $pos2 = random_int(0, $length - 1);
        } while ($pos2 === $pos1);

        $chars[$pos1] = strtoupper($chars[$pos1]);
        $chars[$pos2] = strtoupper($chars[$pos2]);

        return implode('', $chars);
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
