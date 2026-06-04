<?php

declare(strict_types=1);

namespace App\Livewire\Games;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use App\Models\Game;
use App\Services\IgdbService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GameIgdbSearch extends Component
{
    public string $step = 'search';

    // Search state
    public string $query = '';

    public string $platformFilter = '';

    /** @var list<array<string, mixed>> */
    public array $searchResults = [];

    public int $currentPage = 1;

    public bool $hasMorePages = false;

    public const PLATFORM_OPTIONS = [
        '' => 'All Platforms',
        '6' => 'PC',
        '14' => 'Mac',
        '3' => 'Linux',
        '167' => 'PS5',
        '48' => 'PS4',
        '9' => 'PS3',
        '8' => 'PS2',
        '7' => 'PS1',
        '169' => 'Xbox Series X|S',
        '49' => 'Xbox One',
        '12' => 'Xbox 360',
        '11' => 'Xbox',
        '130' => 'Nintendo Switch',
        '41' => 'Wii U',
        '5' => 'Wii',
        '21' => 'GameCube',
        '4' => 'N64',
        '19' => 'SNES',
        '18' => 'NES',
        '37' => '3DS',
        '20' => 'Nintendo DS',
        '24' => 'GBA',
        '33' => 'Game Boy',
        '29' => 'Sega Genesis',
        '32' => 'Sega Saturn',
        '23' => 'Dreamcast',
        '38' => 'PSP',
        '46' => 'PS Vita',
        '34' => 'Android',
        '39' => 'iOS',
        '170' => 'Google Stadia',
    ];

    // Selected game configuration
    public string $title = '';

    public ?string $summary = null;

    public string $cover_url = '';

    public ?string $developer = null;

    public ?string $publisher = null;

    /** @var array<array-key, mixed> */
    public array $availablePlatforms = [];

    /** @var array<int, string> */
    public array $selectedPlatforms = [];

    public string $customPlatformInput = '';

    /** @var array<array-key, mixed> */
    public array $genre = [];

    public ?string $release_date = null;

    public string $status = 'want_to_play';

    public string $ownership = 'not_owned';

    public ?int $rating = null;

    public ?int $igdb_id = null;

    // Duplicate detection
    /** @var array<array-key, mixed> */
    public array $existingIgdbIds = [];

    public function mount(): void
    {
        $this->existingIgdbIds = Game::where('user_id', Auth::id())
            ->whereNotNull('igdb_id')
            ->pluck('igdb_id')
            ->all();
    }

    public function search(): void
    {
        $query = trim($this->query);
        if ($query === '') {
            return;
        }

        $platformId = $this->platformFilter !== '' ? (int) $this->platformFilter : null;
        $result = app(IgdbService::class)->search($query, 1, 20, $platformId);

        $this->searchResults = $result['results'];
        $this->hasMorePages = count($result['results']) >= 20;
        $this->currentPage = 1;
        $this->step = 'results';
    }

    public function loadPage(int $page): void
    {
        $platformId = $this->platformFilter !== '' ? (int) $this->platformFilter : null;
        $result = app(IgdbService::class)->search(trim($this->query), $page, 20, $platformId);

        $this->searchResults = $result['results'];
        $this->hasMorePages = count($result['results']) >= 20;
        $this->currentPage = $page;
    }

    public function selectResult(int $index): void
    {
        $result = $this->searchResults[$index] ?? null;
        if (! $result) {
            return;
        }

        $this->igdb_id = $this->intOrNull($result['igdb_id'] ?? null);
        $this->title = $this->strOf($result['title'] ?? null);
        $this->summary = $this->strOrNull($result['summary'] ?? null);
        $this->cover_url = $this->strOf($result['cover_url'] ?? null);
        $this->developer = $this->strOrNull($result['developer'] ?? null);
        $this->publisher = $this->strOrNull($result['publisher'] ?? null);
        $this->availablePlatforms = is_array($result['platforms'] ?? null) ? $result['platforms'] : [];
        $this->genre = is_array($result['genres'] ?? null) ? $result['genres'] : [];
        $this->release_date = $this->strOrNull($result['release_date'] ?? null);
        $this->status = 'backlog';
        $this->ownership = 'not_owned';
        $this->rating = null;
        $this->customPlatformInput = '';

        // Pre-select the filtered platform if one was used
        $this->selectedPlatforms = [];
        if ($this->platformFilter !== '') {
            $filterLabel = self::PLATFORM_OPTIONS[$this->platformFilter] ?? null;
            if ($filterLabel) {
                foreach ($this->availablePlatforms as $plat) {
                    if (! is_string($plat)) {
                        continue;
                    }
                    if ($plat === $filterLabel || str_contains($plat, $filterLabel)) {
                        $this->selectedPlatforms = [$plat];
                        break;
                    }
                }
            }
        }

        $this->step = 'configure';
    }

    private function strOf(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function strOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    public function togglePlatform(string $platform): void
    {
        if (in_array($platform, $this->selectedPlatforms)) {
            $this->selectedPlatforms = array_values(array_diff($this->selectedPlatforms, [$platform]));
        } else {
            $this->selectedPlatforms[] = $platform;
        }
    }

    public function addCustomPlatform(): void
    {
        $platform = trim($this->customPlatformInput);
        if ($platform !== '' && ! in_array($platform, $this->selectedPlatforms)) {
            $this->selectedPlatforms[] = $platform;
        }
        $this->customPlatformInput = '';
    }

    public function removeSelectedPlatform(int $index): void
    {
        unset($this->selectedPlatforms[$index]);
        $this->selectedPlatforms = array_values($this->selectedPlatforms);
    }

    public function addGame(): void
    {
        $game = Game::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'description' => $this->summary ?: null,
            'cover_url' => $this->cover_url ?: null,
            'developer' => $this->developer ?: null,
            'publisher' => $this->publisher ?: null,
            'platform' => $this->selectedPlatforms === [] ? null : $this->selectedPlatforms,
            'genre' => $this->genre === [] ? null : $this->genre,
            'release_date' => $this->release_date,
            'status' => $this->status,
            'ownership' => $this->ownership,
            'rating' => $this->rating,
            'igdb_id' => $this->igdb_id,
        ]);

        session()->flash('message', "Added \"{$this->title}\" to your library.");
        $this->redirect(route('games.show', $game));
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

    /**
     * @param  array<string, mixed>  $result
     */
    public function isResultDuplicate(array $result): bool
    {
        $igdbId = $result['igdb_id'] ?? null;

        return $igdbId && in_array($igdbId, $this->existingIgdbIds);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.games.game-igdb-search', [
            'statuses' => PlayingStatus::cases(),
            'ownershipStatuses' => OwnershipStatus::cases(),
            'platformOptions' => self::PLATFORM_OPTIONS,
        ]);
    }
}
