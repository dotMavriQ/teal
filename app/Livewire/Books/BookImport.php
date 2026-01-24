<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\FetchBookCover;
use App\Jobs\ImportFromJson;
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
        $mimes = $this->format === 'json' ? 'json' : 'csv,txt';
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

        if ($this->format === 'json') {
            $service = new JsonImportService();
            $books = $service->parseJson($content);
        } else {
            $service = new GoodReadsImportService();
            $books = $service->parseCSV($content);
        }

        $this->preview = $books->take(10);
    }

    public function import(): void
    {
        $this->validate();
        $this->importing = true;

        $content = file_get_contents($this->file->getRealPath());

        if ($this->format === 'json') {
            ImportFromJson::dispatch(
                Auth::id(),
                $content,
                $this->skipDuplicates
            );
            $this->importResult = [
                'imported' => 0,
                'skipped' => 0,
                'errors' => [],
                'async' => true,
                'message' => 'Your books are being imported in the background. Check back shortly!',
            ];
        } else {
            $service = new GoodReadsImportService();
            $books = $service->parseCSV($content);

            $this->importResult = $service->importBooks(
                Auth::user(),
                $books,
                $this->skipDuplicates
            );

            // Dispatch cover fetch jobs for imported books
            $this->coverJobsDispatched = $this->dispatchCoverFetchJobs($this->importResult['book_ids'] ?? []);
        }

        $this->importing = false;
        $this->preview = null;
        $this->file = null;
    }

    protected function dispatchCoverFetchJobs(array $bookIds): int
    {
        $dispatched = 0;

        foreach ($bookIds as $bookId) {
            $book = Book::find($bookId);

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
        $this->format = 'csv';
    }

    public function render()
    {
        return view('livewire.books.book-import')->layout('layouts.app');
    }
}
