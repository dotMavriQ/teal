<?php

declare(strict_types=1);

namespace App\Livewire\Anime;

use App\Models\User;
use App\Services\MalImportService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

class AnimeImport extends Component
{
    use WithFileUploads;

    public string $importMode = 'username';

    public string $malUsername = '';

    public ?TemporaryUploadedFile $file = null;

    public bool $skipDuplicates = true;

    /** @var Collection<int, array<string, mixed>>|null */
    public ?Collection $preview = null;

    /** @var array{imported: int, skipped: int, errors: list<string>}|null */
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
        } catch (Exception $e) {
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
        $content = $this->uploadedContent();

        if ($content === null) {
            return;
        }

        try {
            $service = new MalImportService;
            $entries = $service->parseXml($content);

            $this->totalEntries = $entries->count();
            $this->preview = $entries->take(5);
        } catch (Exception $e) {
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
                $content = $this->uploadedContent();

                if ($content === null) {
                    throw new RuntimeException('Could not read the uploaded file.');
                }

                $entries = $service->parseXml($content);
            }

            $this->importResult = $service->importAll(
                $this->currentUser(),
                $entries,
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
        return view('livewire.anime.anime-import');
    }
}
