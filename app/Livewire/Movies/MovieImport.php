<?php

declare(strict_types=1);

namespace App\Livewire\Movies;

use App\Models\User;
use App\Services\ImdbImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class MovieImport extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    public bool $skipDuplicates = true;

    /** @var Collection<string, mixed>|null */
    public ?Collection $preview = null;

    /** @var array<string, mixed>|null */
    public ?array $importResult = null;

    public bool $importing = false;

    /**
     * @return array<string, mixed>
     */
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
        $content = $this->uploadedContent();

        if ($content === null) {
            return;
        }

        try {
            $categorized = (new ImdbImportService)->parseCSV($content);

            // Take first 5 of each type for preview
            $movies = $categorized->get('movies') ?? collect();
            $shows = $categorized->get('shows') ?? collect();
            $episodes = $categorized->get('episodes') ?? collect();

            /** @var Collection<string, mixed> $preview */
            $preview = collect();
            if ($movies->count() > 0) {
                $preview['movies'] = $movies->take(5);
            }
            if ($shows->count() > 0) {
                $preview['shows'] = $shows->take(5);
            }
            if ($episodes->count() > 0) {
                $preview['episodes'] = $episodes->take(5);
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
            $content = $this->uploadedContent();

            if ($content === null) {
                throw new \RuntimeException('Could not read the uploaded file.');
            }

            $service = new ImdbImportService;

            $this->importResult = $service->importAll(
                $this->currentUser(),
                $service->parseCSV($content),
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

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.movies.movie-import');
    }
}
