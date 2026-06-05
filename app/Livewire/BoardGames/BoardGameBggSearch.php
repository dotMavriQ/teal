<?php

declare(strict_types=1);

namespace App\Livewire\BoardGames;

use App\Enums\BoardGameStatus;
use App\Models\BoardGame;
use App\Services\BggService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class BoardGameBggSearch extends Component
{
    public string $step = 'search';

    public string $query = '';

    /** @var list<array<string, mixed>> */
    public array $searchResults = [];

    public ?int $selectedBggId = null;

    // Editable fields populated from BGG
    public string $title = '';

    public string $designer = '';

    public string $publisher = '';

    public string $description = '';

    public string $cover_url = '';

    public ?int $year_published = null;

    public ?int $min_players = null;

    public ?int $max_players = null;

    public ?int $playing_time = null;

    /** @var array<array-key, mixed> */
    public array $genre = [];

    public ?float $bgg_rating = null;

    // User fields
    public string $status = 'owned';

    public ?int $rating = null;

    public string $notes = '';

    public function search(): void
    {
        if (trim($this->query) === '') {
            return;
        }

        $bgg = app(BggService::class);
        $this->searchResults = $bgg->search($this->query);
        $this->step = 'results';
    }

    public function selectGame(int $bggId): void
    {
        $bgg = app(BggService::class);
        $details = $bgg->getDetails($bggId);

        if (! $details) {
            session()->flash('error', 'Could not fetch board game details.');

            return;
        }

        $this->selectedBggId = $this->intOrNull($details['bgg_id'] ?? null);
        $this->title = $this->strOf($details['title'] ?? null);
        $this->designer = $this->strOf($details['designer'] ?? null);
        $this->publisher = $this->strOf($details['publisher'] ?? null);
        $this->description = $this->strOf($details['description'] ?? null);
        $this->cover_url = $this->strOf($details['cover_url'] ?? null);
        $this->year_published = $this->intOrNull($details['year_published'] ?? null);
        $this->min_players = $this->intOrNull($details['min_players'] ?? null);
        $this->max_players = $this->intOrNull($details['max_players'] ?? null);
        $this->playing_time = $this->intOrNull($details['playing_time'] ?? null);
        $this->genre = is_array($details['genres'] ?? null) ? $details['genres'] : [];
        $this->bgg_rating = $this->floatOrNull($details['bgg_rating'] ?? null);

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

    private function floatOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    public function save(): void
    {
        if (! $this->selectedBggId) {
            return;
        }

        $boardGame = BoardGame::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'genre' => $this->genre === [] ? null : $this->genre,
            'description' => $this->description ?: null,
            'cover_url' => $this->cover_url ?: null,
            'year_published' => $this->year_published,
            'designer' => $this->designer ?: null,
            'publisher' => $this->publisher ?: null,
            'min_players' => $this->min_players,
            'max_players' => $this->max_players,
            'playing_time' => $this->playing_time,
            'status' => $this->status,
            'rating' => $this->rating,
            'bgg_rating' => $this->bgg_rating,
            'bgg_id' => $this->selectedBggId,
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('message', "{$boardGame->title} added to your collection!");
        $this->redirect(route('board-games.show', $boardGame));
    }

    public function backToResults(): void
    {
        $this->resetConfigureFields();
        $this->step = 'results';
    }

    public function backToSearch(): void
    {
        $this->searchResults = [];
        $this->resetConfigureFields();
        $this->step = 'search';
    }

    private function resetConfigureFields(): void
    {
        $this->selectedBggId = null;
        $this->title = '';
        $this->designer = '';
        $this->publisher = '';
        $this->description = '';
        $this->cover_url = '';
        $this->year_published = null;
        $this->min_players = null;
        $this->max_players = null;
        $this->playing_time = null;
        $this->genre = [];
        $this->bgg_rating = null;
        $this->rating = null;
        $this->notes = '';
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.board-games.board-game-bgg-search', [
            'statuses' => BoardGameStatus::cases(),
        ]);
    }
}
