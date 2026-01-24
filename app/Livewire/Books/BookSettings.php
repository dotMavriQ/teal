<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\FetchBookCover;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class BookSettings extends Component
{
    // Delete all modal
    public bool $showDeleteAllModal = false;
    public string $confirmationWord = '';
    public string $confirmationInput = '';

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

        $pos1 = random_int(0, $length - 1);
        do {
            $pos2 = random_int(0, $length - 1);
        } while ($pos2 === $pos1);

        $chars[$pos1] = strtoupper($chars[$pos1]);
        $chars[$pos2] = strtoupper($chars[$pos2]);

        return implode('', $chars);
    }

    public function recacheCovers(): void
    {
        // Get all books with ISBNs
        $books = Book::query()
            ->where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereNotNull('isbn')
                    ->orWhereNotNull('isbn13');
            })
            ->get();

        $count = 0;

        foreach ($books as $book) {
            // Delete existing local cover file if it exists
            if ($book->cover_url && str_starts_with($book->cover_url, '/storage/covers/')) {
                $filename = str_replace('/storage/', '', $book->cover_url);
                Storage::disk('public')->delete($filename);
            }

            // Clear cover_url
            $book->update(['cover_url' => null]);

            // Dispatch job to fetch fresh cover
            FetchBookCover::dispatch($book->id);
            $count++;
        }

        session()->flash('message', "Re-caching covers for {$count} book(s). This runs in the background.");
    }

    public function render()
    {
        return view('livewire.books.book-settings')
            ->layout('layouts.app');
    }
}
