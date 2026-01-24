<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\FetchBookCover;
use App\Models\Book;
use App\Services\GoodReadsImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class BookImport extends Component
{
    use WithFileUploads;

    public $file;
    public bool $skipDuplicates = true;
    public ?Collection $preview = null;
    public ?array $importResult = null;
    public bool $importing = false;
    public int $coverJobsDispatched = 0;

    protected $rules = [
        'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
    ];

    public function updatedFile(): void
    {
        $this->validate();
        $this->generatePreview();
    }

    protected function generatePreview(): void
    {
        $content = file_get_contents($this->file->getRealPath());
        $service = new GoodReadsImportService();
        $books = $service->parseCSV($content);

        $this->preview = $books->take(10);
    }

    public function import(): void
    {
        $this->validate();

        $this->importing = true;

        $content = file_get_contents($this->file->getRealPath());
        $service = new GoodReadsImportService();
        $books = $service->parseCSV($content);

        $this->importResult = $service->importBooks(
            Auth::user(),
            $books,
            $this->skipDuplicates
        );

        // Dispatch cover fetch jobs for imported books
        $this->coverJobsDispatched = $this->dispatchCoverFetchJobs($this->importResult['book_ids'] ?? []);

        $this->importing = false;
        $this->preview = null;
        $this->file = null;
    }

    protected function dispatchCoverFetchJobs(array $bookIds): int
    {
        $dispatched = 0;

        foreach ($bookIds as $bookId) {
            $book = Book::find($bookId);

            // Only dispatch for books with ISBN (can potentially have a cover)
            if ($book && ($book->isbn || $book->isbn13)) {
                FetchBookCover::dispatch($bookId);
                $dispatched++;
            }
        }

        return $dispatched;
    }

    public function resetForm(): void
    {
        $this->file = null;
        $this->preview = null;
        $this->importResult = null;
        $this->coverJobsDispatched = 0;
    }

    public function render()
    {
        return view('livewire.books.book-import')->layout('layouts.app');
    }
}
