<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\FetchBookCover;
use App\Models\Book;
use App\Services\GoodReadsImportService;
use App\Services\JsonImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class BookImport extends Component
{
    use WithFileUploads;

    public $file;

    public string $format = 'csv'; // csv or json

    public bool $skipDuplicates = true;

    public ?Collection $preview = null;

    public ?array $importResult = null;

    public bool $importing = false;

    public int $coverJobsDispatched = 0;

    protected function rules(): array
    {
        $mimes = $this->format === 'json' ? 'json,txt' : 'csv,txt';

        return [
            'file' => ['required', 'file', "mimes:{$mimes}", 'max:10240'],
        ];
    }

    public function updatedFile(): void
    {
        $this->validate();
        $this->generatePreview();
    }

    public function updatedFormat(): void
    {
        $this->file = null;
        $this->preview = null;
        $this->importResult = null;
    }

    protected function generatePreview(): void
    {
        $content = file_get_contents($this->file->getRealPath());

        try {
            if ($this->format === 'json') {
                $service = new JsonImportService;
                $books = $service->parseJson($content);
            } else {
                $service = new GoodReadsImportService;
                $books = $service->parseCSV($content);
            }

            $this->preview = $books->take(10);
        } catch (\InvalidArgumentException $e) {
            $this->addError('file', $e->getMessage());
            $this->file = null;
            $this->preview = null;
        }
    }

    public function import(): void
    {
        $this->validate();
        $this->importing = true;

        try {
            $content = file_get_contents($this->file->getRealPath());

            if ($this->format === 'json') {
                $service = new JsonImportService;
                $books = $service->parseJson($content);

                $this->importResult = $service->importBooks(
                    Auth::user(),
                    $books,
                    $this->skipDuplicates
                );
            } else {
                $service = new GoodReadsImportService;
                $books = $service->parseCSV($content);

                $this->importResult = $service->importBooks(
                    Auth::user(),
                    $books,
                    $this->skipDuplicates
                );
            }

            // Dispatch cover fetch jobs for imported books (runs after response)
            $this->coverJobsDispatched = $this->dispatchCoverFetchJobs($this->importResult['book_ids'] ?? []);
        } catch (\Exception $e) {
            $this->importResult = [
                'imported' => 0,
                'skipped' => 0,
                'errors' => [$e->getMessage()],
                'book_ids' => [],
            ];
        } finally {
            $this->importing = false;
            $this->preview = null;
            $this->file = null;
        }
    }

    protected function dispatchCoverFetchJobs(array $bookIds): int
    {
        $dispatched = 0;

        foreach ($bookIds as $bookId) {
            $book = Book::find($bookId);

            if (! $book) {
                continue;
            }

            // Check if book has external cover URL or ISBN for lookup
            $hasExternalUrl = $book->cover_url && filter_var($book->cover_url, FILTER_VALIDATE_URL);
            $hasIsbn = $book->isbn || $book->isbn13;

            if ($hasExternalUrl || $hasIsbn) {
                // Dispatch after response - runs in background after user sees result
                FetchBookCover::dispatchAfterResponse($bookId, $hasExternalUrl ? $book->cover_url : null);
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
        $this->format = 'csv';
    }

    public function render()
    {
        return view('livewire.books.book-import')->layout('layouts.app');
    }
}
