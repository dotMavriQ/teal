<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\ImportFromJson;
use App\Models\Book;
use App\Services\JsonImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class JsonImport extends Component
{
    use WithFileUploads;

    public $file;
    public bool $skipDuplicates = true;
    public ?Collection $preview = null;
    public ?array $importResult = null;
    public bool $importing = false;
    public string $importStatus = '';
    public int $jobId = 0;

    protected $rules = [
        'file' => ['required', 'file', 'mimes:json', 'max:10240'],
    ];

    public function updatedFile(): void
    {
        $this->validate();
        $this->generatePreview();
    }

    protected function generatePreview(): void
    {
        try {
            $content = file_get_contents($this->file->getRealPath());
            $service = new JsonImportService();
            $books = $service->parseJson($content);

            if ($books->isEmpty()) {
                $this->preview = null;
                $this->importStatus = 'No valid books found in JSON file.';
                return;
            }

            $this->preview = $books->take(10);
            $this->importStatus = "Found " . $books->count() . " books in JSON file";
        } catch (\Exception $e) {
            $this->importStatus = "Error parsing JSON: " . $e->getMessage();
            $this->preview = null;
        }
    }

    public function import(): void
    {
        $this->validate();

        $this->importing = true;
        $this->importStatus = 'Queuing import job...';

        try {
            $content = file_get_contents($this->file->getRealPath());

            ImportFromJson::dispatch(
                Auth::id(),
                $content,
                $this->skipDuplicates
            );

            $this->importStatus = 'Import job queued! Books will be imported in the background.';
            $this->importing = false;
            $this->preview = null;
            $this->file = null;

            $this->dispatch('import-queued');
        } catch (\Exception $e) {
            $this->importStatus = 'Error: ' . $e->getMessage();
            $this->importing = false;
        }
    }

    public function resetForm(): void
    {
        $this->file = null;
        $this->preview = null;
        $this->importResult = null;
        $this->importStatus = '';
        $this->jobId = 0;
    }

    public function render()
    {
        return view('livewire.books.json-import')->layout('layouts.app');
    }
}
