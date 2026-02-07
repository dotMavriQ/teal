<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Services\ImdbImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class MovieImport extends Component
{
    use WithFileUploads;

    public $file;

    public bool $skipDuplicates = true;

    public ?Collection $preview = null;

    public ?array $importResult = null;

    public bool $importing = false;

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }

    public function updatedFile(): void
    {
        $this->validate();
        $this->generatePreview();
    }

    protected function generatePreview(): void
    {
        $content = file_get_contents($this->file->getRealPath());

        try {
            $service = new ImdbImportService;
            $categorized = $service->parseCSV($content);

            // Take first 5 of each type for preview
            $preview = collect();
            if ($categorized['movies']->count() > 0) {
                $preview['movies'] = $categorized['movies']->take(5);
            }
            if ($categorized['shows']->count() > 0) {
                $preview['shows'] = $categorized['shows']->take(5);
            }
            if ($categorized['episodes']->count() > 0) {
                $preview['episodes'] = $categorized['episodes']->take(5);
            }

            $this->preview = $preview->count() > 0 ? $preview : null;
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

            $service = new ImdbImportService;
            $categorized = $service->parseCSV($content);

            $this->importResult = $service->importAll(
                Auth::user(),
                $categorized,
                $this->skipDuplicates
            );
        } catch (\Exception $e) {
            $this->importResult = [
                'imported' => 0,
                'skipped' => 0,
                'errors' => [$e->getMessage()],
                'details' => [],
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
    }

    public function render()
    {
        return view('livewire.movies.movie-import');
    }
}
