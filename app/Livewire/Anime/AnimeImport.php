<?php

declare(strict_types=1);

namespace App\Livewire\Anime;

use App\Services\MalImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class AnimeImport extends Component
{
    use WithFileUploads;

    public string $importMode = 'username';

    public string $malUsername = '';

    public $file;

    public bool $skipDuplicates = true;

    public ?Collection $preview = null;

    public ?array $importResult = null;

    public bool $importing = false;

    public int $totalEntries = 0;

    public function setImportMode(string $mode): void
    {
        $this->importMode = in_array($mode, ['username', 'xml']) ? $mode : 'username';
        $this->resetForm();
    }

    public function fetchFromMal(): void
    {
        if (empty(trim($this->malUsername))) {
            $this->addError('malUsername', 'Please enter a MAL username.');

            return;
        }

        $this->importing = true;

        try {
            $service = new MalImportService;
            $entries = $service->fetchFromMal(trim($this->malUsername));

            $this->totalEntries = $entries->count();
            $this->preview = $entries->take(5);
        } catch (\Exception $e) {
            $this->addError('malUsername', $e->getMessage());
            $this->preview = null;
        } finally {
            $this->importing = false;
        }
    }

    public function updatedFile(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);
        $this->generateXmlPreview();
    }

    protected function generateXmlPreview(): void
    {
        $content = file_get_contents($this->file->getRealPath());

        try {
            $service = new MalImportService;
            $entries = $service->parseXml($content);

            $this->totalEntries = $entries->count();
            $this->preview = $entries->take(5);
        } catch (\Exception $e) {
            $this->addError('file', $e->getMessage());
            $this->file = null;
            $this->preview = null;
        }
    }

    public function import(): void
    {
        $this->importing = true;

        try {
            $service = new MalImportService;

            if ($this->importMode === 'username') {
                $entries = $service->fetchFromMal(trim($this->malUsername));
            } else {
                $content = file_get_contents($this->file->getRealPath());
                $entries = $service->parseXml($content);
            }

            $this->importResult = $service->importAll(
                Auth::user(),
                $entries,
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
        }
    }

    public function resetForm(): void
    {
        $this->malUsername = '';
        $this->file = null;
        $this->preview = null;
        $this->importResult = null;
        $this->totalEntries = 0;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.anime.anime-import')
            ->layout('layouts.app');
    }
}
