<?php

declare(strict_types=1);

namespace App\Livewire\Books;

use App\Jobs\ImportFromJson;
use App\Models\User;
use App\Services\JsonImportService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

class JsonImport extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    public bool $skipDuplicates = true;

    /** @var Collection<int, array<string, mixed>>|null */
    public ?Collection $preview = null;

    /** @var array<string, mixed>|null */
    public ?array $importResult = null;

    public bool $importing = false;

    public string $importStatus = '';

    public int $jobId = 0;

    /** @var array<string, mixed> */
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
            $content = $this->uploadedContent();

            if ($content === null) {
                $this->importStatus = 'Could not read the uploaded file.';
                $this->preview = null;

                return;
            }

            $service = new JsonImportService;
            $books = $service->parseJson($content);

            if ($books->isEmpty()) {
                $this->preview = null;
                $this->importStatus = 'No valid books found in JSON file.';

                return;
            }

            $this->preview = $books->take(10);
            $this->importStatus = 'Found '.$books->count().' books in JSON file';
        } catch (Exception $e) {
            $this->importStatus = 'Error parsing JSON: '.$e->getMessage();
            $this->preview = null;
        }
    }

    public function import(): void
    {
        $this->validate();

        $this->importing = true;
        $this->importStatus = 'Queuing import job...';

        try {
            $content = $this->uploadedContent();

            if ($content === null) {
                throw new RuntimeException('Could not read the uploaded file.');
            }

            dispatch(new ImportFromJson($this->currentUser()->id, $content, $this->skipDuplicates));

            $this->importStatus = 'Import job queued! Books will be imported in the background.';
            $this->importing = false;
            $this->preview = null;
            $this->file = null;

            $this->dispatch('import-queued');
        } catch (Exception $e) {
            $this->importStatus = 'Error: '.$e->getMessage();
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
    public function render(): View
    {
        return view('livewire.books.json-import');
    }
}
