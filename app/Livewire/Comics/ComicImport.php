<?php

declare(strict_types=1);

namespace App\Livewire\Comics;

use App\Services\ComicImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ComicImport extends Component
{
    use WithFileUploads;

    public $file;

    public string $format = 'csv'; // csv or json

    public bool $skipDuplicates = true;

    public ?Collection $preview = null;

    public ?array $importResult = null;

    public bool $importing = false;

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
            $service = new ComicImportService;

            if ($this->format === 'json') {
                $comics = $service->parseJson($content);
            } else {
                $comics = $service->parseCSV($content);
            }

            $this->preview = $comics->take(10);
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
            $service = new ComicImportService;

            if ($this->format === 'json') {
                $comics = $service->parseJson($content);
            } else {
                $comics = $service->parseCSV($content);
            }

            $this->importResult = $service->importComics(
                Auth::user(),
                $comics,
                $this->skipDuplicates
            );
        } catch (\Exception $e) {
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

    public function render()
    {
        return view('livewire.comics.comic-import')->layout('layouts.app');
    }
}
