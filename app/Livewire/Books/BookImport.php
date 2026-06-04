<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\FetchBookCover;
use App\Models\Book;
use App\Models\User;
use App\Services\GoodReadsImportService;
use App\Services\JsonImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class BookImport extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    public string $format = 'csv'; // csv or json

    public bool $skipDuplicates = true;

    /** @var Collection<int, array<string, mixed>>|null */
    public ?Collection $preview = null;

    /** @var array{imported: int, skipped: int, errors: list<string>, book_ids: list<int>}|null */
    public ?array $importResult = null;

    public bool $importing = false;

    public int $coverJobsDispatched = 0;

    /**
     * @return array<string, mixed>
     */
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
        $content = $this->uploadedContent();

        if ($content === null) {
            return;
        }

        try {
            if ($this->format === 'json') {
                $books = (new JsonImportService)->parseJson($content);
            } else {
                $books = (new GoodReadsImportService)->parseCSV($content);
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
            $content = $this->uploadedContent();

            if ($content === null) {
                throw new \RuntimeException('Could not read the uploaded file.');
            }

            $user = $this->currentUser();

            if ($this->format === 'json') {
                $service = new JsonImportService;
                $this->importResult = $service->importBooks($user, $service->parseJson($content), $this->skipDuplicates);
            } else {
                $service = new GoodReadsImportService;
                $this->importResult = $service->importBooks($user, $service->parseCSV($content), $this->skipDuplicates);
            }

            // Dispatch cover fetch jobs for imported books (runs after response)
            $this->coverJobsDispatched = $this->dispatchCoverFetchJobs($this->importResult['book_ids']);
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

    /**
     * @param  list<int>  $bookIds
     */
    protected function dispatchCoverFetchJobs(array $bookIds): int
    {
        $dispatched = 0;
        $books = Book::whereIn('id', $bookIds)->get();

        foreach ($books as $book) {
            $hasExternalUrl = $book->cover_url && filter_var($book->cover_url, FILTER_VALIDATE_URL);
            $hasIsbn = $book->isbn || $book->isbn13;

            if ($hasExternalUrl || $hasIsbn) {
                FetchBookCover::dispatchAfterResponse($book->id, $hasExternalUrl ? $book->cover_url : null);
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

    private function uploadedContent(): ?string
    {
        $path = $this->file?->getRealPath();

        if (! is_string($path) || $path === '') {
            return null;
        }

        $content = file_get_contents($path);

        return $content === false ? null : $content;
    }

    private function currentUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }

    #[Layout('layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.books.book-import');
    }
}
