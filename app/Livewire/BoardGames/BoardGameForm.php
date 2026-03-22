<?php

declare(strict_types=1);

namespace App\Livewire\BoardGames;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use App\Models\BoardGame;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class BoardGameForm extends Component
{
    use AuthorizesRequests;

    public ?BoardGame $boardGame = null;

    public string $title = '';

    public array $genre = [];

    public string $genreInput = '';

    public string $description = '';

    public string $cover_url = '';

    public ?int $year_published = null;

    public string $designer = '';

    public string $publisher = '';

    public ?int $min_players = null;

    public ?int $max_players = null;

    public ?int $playing_time = null;

    public string $status = 'backlog';

    public string $ownership = 'owned';

    public ?int $rating = null;

    public ?int $plays = null;

    public ?int $bgg_id = null;

    public ?string $date_started = null;

    public ?string $date_finished = null;

    public string $notes = '';

    public function mount(?BoardGame $boardGame = null): void
    {
        if ($boardGame && $boardGame->exists) {
            $this->authorize('update', $boardGame);
            $this->boardGame = $boardGame;
            $this->fill([
                'title' => $boardGame->title,
                'genre' => $boardGame->genre ?? [],
                'description' => $boardGame->description ?? '',
                'cover_url' => $boardGame->cover_url ?? '',
                'year_published' => $boardGame->year_published,
                'designer' => $boardGame->designer ?? '',
                'publisher' => $boardGame->publisher ?? '',
                'min_players' => $boardGame->min_players,
                'max_players' => $boardGame->max_players,
                'playing_time' => $boardGame->playing_time,
                'status' => $boardGame->status->value,
                'ownership' => $boardGame->ownership->value,
                'rating' => $boardGame->rating,
                'plays' => $boardGame->plays,
                'bgg_id' => $boardGame->bgg_id,
                'date_started' => $boardGame->date_started?->format('d/m/Y'),
                'date_finished' => $boardGame->date_finished?->format('d/m/Y'),
                'notes' => $boardGame->notes ?? '',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'genre' => ['nullable', 'array'],
            'genre.*' => ['string', 'max:100'],
            'description' => ['nullable', 'string', 'max:10000'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'year_published' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'designer' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'min_players' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_players' => ['nullable', 'integer', 'min:1', 'max:100'],
            'playing_time' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', Rule::enum(PlayingStatus::class)],
            'ownership' => ['required', Rule::enum(OwnershipStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'plays' => ['nullable', 'integer', 'min:0'],
            'bgg_id' => ['nullable', 'integer'],
            'date_started' => ['nullable', 'date_format:d/m/Y'],
            'date_finished' => ['nullable', 'date_format:d/m/Y'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    protected function parseDateInput(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return checkdate((int) $matches[2], (int) $matches[1], (int) $matches[3])
                ? "{$matches[3]}-{$matches[2]}-{$matches[1]}"
                : null;
        }

        return null;
    }

    public function addGenre(): void
    {
        $genre = trim($this->genreInput);
        if ($genre !== '' && ! in_array($genre, $this->genre)) {
            $this->genre[] = $genre;
        }
        $this->genreInput = '';
    }

    public function removeGenre(int $index): void
    {
        unset($this->genre[$index]);
        $this->genre = array_values($this->genre);
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['date_started'] = $this->parseDateInput($validated['date_started'] ?? null);
        $validated['date_finished'] = $this->parseDateInput($validated['date_finished'] ?? null);

        $data = [
            'title' => $validated['title'],
            'genre' => ! empty($validated['genre']) ? $validated['genre'] : null,
            'description' => $validated['description'] ?: null,
            'cover_url' => $validated['cover_url'] ?: null,
            'year_published' => $validated['year_published'],
            'designer' => $validated['designer'] ?: null,
            'publisher' => $validated['publisher'] ?: null,
            'min_players' => $validated['min_players'],
            'max_players' => $validated['max_players'],
            'playing_time' => $validated['playing_time'],
            'status' => $validated['status'],
            'ownership' => $validated['ownership'],
            'rating' => $validated['rating'],
            'plays' => $validated['plays'],
            'bgg_id' => $validated['bgg_id'],
            'date_started' => $validated['date_started'],
            'date_finished' => $validated['date_finished'],
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->boardGame) {
            $this->boardGame->update($data);
            $message = 'Board game updated successfully.';
        } else {
            $data['user_id'] = Auth::id();
            $this->boardGame = BoardGame::create($data);
            $message = 'Board game created successfully.';
        }

        session()->flash('message', $message);

        $this->redirect(route('board-games.show', $this->boardGame));
    }

    public function isEditing(): bool
    {
        return $this->boardGame !== null && $this->boardGame->exists;
    }

    public function render()
    {
        return view('livewire.board-games.board-game-form', [
            'statuses' => PlayingStatus::cases(),
            'ownershipStatuses' => OwnershipStatus::cases(),
            'isEditing' => $this->isEditing(),
        ])->layout('layouts.app');
    }
}
