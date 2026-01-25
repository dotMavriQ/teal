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

    public ?int $current_page = null;

    public ?string $published_date = null;

    public string $publisher = '';

    public string $status = 'want_to_read';

    public ?int $rating = null;

    public ?string $date_started = null;

    public ?string $date_finished = null;

    public string $notes = '';

    public array $tags = [];

    public string $newTag = '';

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
                'current_page' => $book->current_page,
                'published_date' => $book->published_date?->format('d/m/Y'),
                'publisher' => $book->publisher ?? '',
                'status' => $book->status->value,
                'rating' => $book->rating,
                'date_started' => $book->date_started?->format('d/m/Y'),
                'date_finished' => $book->date_finished?->format('d/m/Y'),
                'notes' => $book->notes ?? '',
                'tags' => $book->tags,
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
            'current_page' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'published_date' => ['nullable', 'date_format:d/m/Y'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ReadingStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'date_started' => ['nullable', 'date_format:d/m/Y'],
            'date_finished' => ['nullable', 'date_format:d/m/Y'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    protected function parseDateInput(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Already in YYYY-MM-DD format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Parse DD/MM/YYYY format
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];

            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        return null;
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Convert dates from DD/MM/YYYY to YYYY-MM-DD if needed
        $validated['published_date'] = $this->parseDateInput($validated['published_date'] ?? null);
        $validated['date_started'] = $this->parseDateInput($validated['date_started'] ?? null);
        $validated['date_finished'] = $this->parseDateInput($validated['date_finished'] ?? null);

        // Validate date_finished >= date_started if both are present
        if ($validated['date_started'] && $validated['date_finished']) {
            if ($validated['date_finished'] < $validated['date_started']) {
                $this->addError('date_finished', 'Date read must be after or equal to date started.');
                return;
            }
        }

        $data = [
            'title' => $validated['title'],
            'author' => $validated['author'] ?: null,
            'isbn' => $validated['isbn'] ?: null,
            'isbn13' => $validated['isbn13'] ?: null,
            'cover_url' => $validated['cover_url'] ?: null,
            'description' => $validated['description'] ?: null,
            'page_count' => $validated['page_count'],
            'current_page' => $validated['current_page'],
            'published_date' => $validated['published_date'],
            'publisher' => $validated['publisher'] ?: null,
            'status' => $validated['status'],
            'rating' => $validated['rating'],
            'date_started' => $validated['date_started'],
            'date_finished' => $validated['date_finished'],
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->book) {
            $this->book->update($data);
            $this->book->setTagsFromArray($this->tags);
            $this->book->save();
            $message = 'Book updated successfully.';
        } else {
            $data['user_id'] = Auth::id();
            $data['date_added'] = now();  // Set date_added only on creation
            $this->book = Book::create($data);
            $this->book->setTagsFromArray($this->tags);
            $this->book->save();
            $message = 'Book created successfully.';
        }

        session()->flash('message', $message);

        $this->redirect(route('books.show', $this->book));
    }

    public function addTag(): void
    {
        $tag = trim($this->newTag);
        if ($tag !== '' && !in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
        $this->newTag = '';
    }

    public function addExistingTag(string $tag): void
    {
        $tag = trim($tag);
        if ($tag !== '' && !in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
    }

    public function removeTag(int $index): void
    {
        unset($this->tags[$index]);
        $this->tags = array_values($this->tags);
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
            'availableTags' => Book::getAllTagsForUser(Auth::id()),
        ])->layout('layouts.app');
    }
}
