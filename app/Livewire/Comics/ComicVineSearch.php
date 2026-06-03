<?php

declare(strict_types=1);

namespace App\Livewire\Comics;

use App\Enums\ReadingStatus;
use App\Models\Comic;
use App\Models\ComicIssue;
use App\Services\ComicVineService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ComicVineSearch extends Component
{
    public string $step = 'search';

    // Search state
    public string $query = '';

    /** @var list<array<string, mixed>> */
    public array $searchResults = [];

    // Selected volume details
    public string $title = '';

    public string $publisher = '';

    public ?int $start_year = null;

    public ?int $issue_count = null;

    public string $description = '';

    public string $cover_url = '';

    public string $comicvine_volume_id = '';

    public string $comicvine_url = '';

    public string $creators = '';

    public string $characters = '';

    public string $status = 'want_to_read';

    public ?int $rating = null;

    public bool $fetchIssues = true;

    // Duplicate detection
    /** @var array<array-key, mixed> */
    public array $existingVolumeIds = [];

    public function mount(): void
    {
        $this->existingVolumeIds = Comic::where('user_id', Auth::id())
            ->whereNotNull('comicvine_volume_id')
            ->pluck('comicvine_volume_id')
            ->all();
    }

    public function search(): void
    {
        $query = trim($this->query);
        if ($query === '') {
            return;
        }

        $comicVine = app(ComicVineService::class);
        $this->searchResults = $comicVine->searchVolumes($query);
        $this->step = 'results';
    }

    public function selectResult(string $volumeId): void
    {
        $comicVine = app(ComicVineService::class);
        $details = $comicVine->fetchVolumeDetails($volumeId);

        if (! $details) {
            session()->flash('error', 'Could not fetch volume details from Comic Vine.');

            return;
        }

        $this->title = $this->strOf($details['title'] ?? null);
        $this->publisher = $this->strOf($details['publisher'] ?? null);
        $this->start_year = $this->intOrNull($details['start_year'] ?? null);
        $this->issue_count = $this->intOrNull($details['issue_count'] ?? null);
        $this->description = $this->strOf($details['description'] ?? null);
        $this->cover_url = $this->strOf($details['cover_url'] ?? null);
        $this->comicvine_volume_id = $this->strOf($details['volume_id'] ?? null);
        $this->comicvine_url = $this->strOf($details['comicvine_url'] ?? null);
        $this->creators = $this->strOf($details['creators'] ?? null);
        $this->characters = $this->strOf($details['characters'] ?? null);
        $this->status = 'want_to_read';
        $this->rating = null;

        $this->step = 'configure';
    }

    private function strOf(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    public function addComic(): void
    {
        $comic = Comic::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'publisher' => $this->publisher ?: null,
            'start_year' => $this->start_year,
            'issue_count' => $this->issue_count,
            'description' => $this->description ?: null,
            'cover_url' => $this->cover_url ?: null,
            'comicvine_volume_id' => $this->comicvine_volume_id ?: null,
            'comicvine_url' => $this->comicvine_url ?: null,
            'creators' => $this->creators ?: null,
            'characters' => $this->characters ?: null,
            'status' => $this->status,
            'rating' => $this->rating,
            'date_added' => now(),
            'metadata_fetched_at' => now(),
        ]);

        if ($this->status === 'reading') {
            $comic->update(['date_started' => now()]);
        } elseif ($this->status === 'read') {
            $comic->update(['date_finished' => now()]);
        }

        if ($this->fetchIssues && $this->comicvine_volume_id) {
            $comicVine = app(ComicVineService::class);
            $issues = $comicVine->fetchVolumeIssues($this->comicvine_volume_id);

            foreach ($issues as $issue) {
                ComicIssue::create([
                    'comic_id' => $comic->id,
                    'user_id' => Auth::id(),
                    'title' => $issue['title'],
                    'issue_number' => $issue['issue_number'],
                    'cover_date' => $issue['cover_date'],
                    'cover_url' => $issue['cover_url'],
                    'description' => $issue['description'],
                    'comicvine_issue_id' => $issue['issue_id'],
                    'comicvine_url' => $issue['comicvine_url'],
                    'status' => 'want_to_read',
                ]);
            }

            $issueCount = count($issues);
            session()->flash('message', "Added \"{$this->title}\" with {$issueCount} issue(s) to your library.");
        } else {
            session()->flash('message', "Added \"{$this->title}\" to your library.");
        }

        $this->redirect(route('comics.show', $comic));
    }

    public function backToSearch(): void
    {
        $this->step = 'search';
        $this->searchResults = [];
        $this->query = '';
    }

    public function backToResults(): void
    {
        $this->step = 'results';
    }

    #[Layout('layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.comics.comic-vine-search', [
            'statuses' => ReadingStatus::cases(),
        ]);
    }
}
