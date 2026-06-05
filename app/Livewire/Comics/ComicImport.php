<?php

declare(strict_types=1);

namespace App\Livewire\Comics;

use App\Models\User;
use App\Services\ComicImportService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

class ComicImport extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    public string $format = 'csv'; // csv or json

    public bool $skipDuplicates = true;

    /** @var Collection<int, array<string, mixed>>|null */
    public ?Collection $preview = null;

    /** @var array{imported: int, skipped: int, errors: list<string>}|null */
    public ?array $importResult = null;

    public bool $importing = false;

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
            $service = new ComicImportService;

            $comics = $this->format === 'json' ? $service->parseJson($content) : $service->parseCSV($content);

            $this->preview = $comics->take(10);
        } catch (InvalidArgumentException $e) {
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
                throw new RuntimeException('Could not read the uploaded file.');
            }

            $service = new ComicImportService;

            $comics = $this->format === 'json' ? $service->parseJson($content) : $service->parseCSV($content);

            $this->importResult = $service->importComics(
                $this->currentUser(),
                $comics,
                $this->skipDuplicates
            );
        } catch (Exception $e) {
            $this->importResult = [
                'imported' => 0,
                'skipped' => 0,
                'errors' => [$e->getMessage()],
            ];
        } finally {
            $this->importing = false;
            $this->preview = null;
            $this->file = null;
        }
    }

    public function resetForm(): void
    {
        $this->file = null;
        $this->preview = null;
        $this->importResult = null;
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
    public function render(): View
    {
        return view('livewire.comics.comic-import');
    }
}
