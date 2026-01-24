<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Enums\ReadingStatus;
use App\Models\Book;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class BookForm extends Component
{
    use AuthorizesRequests;

    public ?Book $book = null;

    public string $title = '';

    public string $author = '';

    public string $isbn = '';

    public string $isbn13 = '';

    public string $cover_url = '';

    public string $description = '';

    public ?int $page_count = null;

    public ?string $published_date = null;

    public string $publisher = '';

    public string $status = 'want_to_read';

    public ?int $rating = null;

    public ?string $date_started = null;

    public ?string $date_finished = null;

    public string $notes = '';

    public function mount(?Book $book = null): void
    {
        if ($book && $book->exists) {
            $this->authorize('update', $book);
            $this->book = $book;
            $this->fill([
                'title' => $book->title,
                'author' => $book->author ?? '',
                'isbn' => $book->isbn ?? '',
                'isbn13' => $book->isbn13 ?? '',
                'cover_url' => $book->cover_url ?? '',
                'description' => $book->description ?? '',
                'page_count' => $book->page_count,
                'published_date' => $book->published_date?->format('Y-m-d'),
                'publisher' => $book->publisher ?? '',
                'status' => $book->status->value,
                'rating' => $book->rating,
                'date_started' => $book->date_started?->format('Y-m-d'),
                'date_finished' => $book->date_finished?->format('Y-m-d'),
                'notes' => $book->notes ?? '',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:13'],
            'isbn13' => ['nullable', 'string', 'max:17'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:10000'],
            'page_count' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'published_date' => ['nullable', 'date'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ReadingStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'date_started' => ['nullable', 'date'],
            'date_finished' => ['nullable', 'date', 'after_or_equal:date_started'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = [
            'title' => $validated['title'],
            'author' => $validated['author'] ?: null,
            'isbn' => $validated['isbn'] ?: null,
            'isbn13' => $validated['isbn13'] ?: null,
            'cover_url' => $validated['cover_url'] ?: null,
            'description' => $validated['description'] ?: null,
            'page_count' => $validated['page_count'],
            'published_date' => $validated['published_date'] ?: null,
            'publisher' => $validated['publisher'] ?: null,
            'status' => $validated['status'],
            'rating' => $validated['rating'],
            'date_started' => $validated['date_started'] ?: null,
            'date_finished' => $validated['date_finished'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->book) {
            $this->book->update($data);
            $message = 'Book updated successfully.';
        } else {
            $data['user_id'] = Auth::id();
            $this->book = Book::create($data);
            $message = 'Book created successfully.';
        }

        session()->flash('message', $message);

        $this->redirect(route('books.show', $this->book));
    }

    public function getStatuses(): array
    {
        return ReadingStatus::cases();
    }

    public function isEditing(): bool
    {
        return $this->book !== null && $this->book->exists;
    }

    public function render()
    {
        return view('livewire.books.book-form', [
            'statuses' => $this->getStatuses(),
            'isEditing' => $this->isEditing(),
        ])->layout('layouts.app');
    }
}
