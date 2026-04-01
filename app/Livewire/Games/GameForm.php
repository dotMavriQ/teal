<?php

declare(strict_types=1);

namespace App\Livewire\Games;

use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;
use App\Models\Game;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class GameForm extends Component
{
    use AuthorizesRequests;

    public ?Game $game = null;

    public string $title = '';

    public array $platform = [];

    public array $genre = [];

    public string $genreInput = '';

    public string $description = '';

    public string $cover_url = '';

    public ?string $release_date = null;

    public string $developer = '';

    public string $publisher = '';

    public string $status = 'backlog';

    public string $ownership = 'not_owned';

    public ?int $rating = null;

    public ?float $hours_played = null;

    public ?int $completion_percentage = null;

    public ?int $igdb_id = null;

    public ?int $rawg_id = null;

    public ?int $mobygames_id = null;

    public ?string $date_started = null;

    public ?string $date_finished = null;

    public string $notes = '';

    public string $platformInput = '';

    public function mount(?Game $game = null): void
    {
        if ($game && $game->exists) {
            $this->authorize('update', $game);
            $this->game = $game;
            $this->fill([
                'title' => $game->title,
                'platform' => $game->platform ?? [],
                'genre' => $game->genre ?? [],
                'description' => $game->description ?? '',
                'cover_url' => $game->cover_url ?? '',
                'release_date' => $game->release_date?->format('d/m/Y'),
                'developer' => $game->developer ?? '',
                'publisher' => $game->publisher ?? '',
                'status' => $game->status->value,
                'ownership' => $game->ownership->value,
                'rating' => $game->rating,
                'hours_played' => $game->hours_played ? (float) $game->hours_played : null,
                'completion_percentage' => $game->completion_percentage,
                'igdb_id' => $game->igdb_id,
                'rawg_id' => $game->rawg_id,
                'mobygames_id' => $game->mobygames_id,
                'date_started' => $game->date_started?->format('d/m/Y'),
                'date_finished' => $game->date_finished?->format('d/m/Y'),
                'notes' => $game->notes ?? '',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'array'],
            'platform.*' => ['string', 'max:50'],
            'genre' => ['nullable', 'array'],
            'genre.*' => ['string', 'max:100'],
            'description' => ['nullable', 'string', 'max:10000'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'release_date' => ['nullable', 'date_format:d/m/Y'],
            'developer' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(PlayingStatus::class)],
            'ownership' => ['required', Rule::enum(OwnershipStatus::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'hours_played' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'completion_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'igdb_id' => ['nullable', 'integer'],
            'rawg_id' => ['nullable', 'integer'],
            'mobygames_id' => ['nullable', 'integer'],
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
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];

            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return "{$year}-{$month}-{$day}";
            }
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

    public function addPlatform(): void
    {
        $platform = trim($this->platformInput);
        if ($platform !== '' && ! in_array($platform, $this->platform)) {
            $this->platform[] = $platform;
        }
        $this->platformInput = '';
    }

    public function removePlatform(int $index): void
    {
        unset($this->platform[$index]);
        $this->platform = array_values($this->platform);
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['release_date'] = $this->parseDateInput($validated['release_date'] ?? null);
        $validated['date_started'] = $this->parseDateInput($validated['date_started'] ?? null);
        $validated['date_finished'] = $this->parseDateInput($validated['date_finished'] ?? null);

        $data = [
            'title' => $validated['title'],
            'platform' => ! empty($validated['platform']) ? $validated['platform'] : null,
            'genre' => ! empty($validated['genre']) ? $validated['genre'] : null,
            'description' => $validated['description'] ?: null,
            'cover_url' => $validated['cover_url'] ?: null,
            'release_date' => $validated['release_date'],
            'developer' => $validated['developer'] ?: null,
            'publisher' => $validated['publisher'] ?: null,
            'status' => $validated['status'],
            'ownership' => $validated['ownership'],
            'rating' => $validated['rating'],
            'hours_played' => $validated['hours_played'],
            'completion_percentage' => $validated['completion_percentage'],
            'igdb_id' => $validated['igdb_id'],
            'rawg_id' => $validated['rawg_id'],
            'mobygames_id' => $validated['mobygames_id'],
            'date_started' => $validated['date_started'],
            'date_finished' => $validated['date_finished'],
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->game) {
            $this->game->update($data);
            $message = 'Game updated successfully.';
        } else {
            $data['user_id'] = Auth::id();
            $this->game = Game::create($data);
            $message = 'Game created successfully.';
        }

        session()->flash('message', $message);

        $this->redirect(route('games.show', $this->game));
    }

    public function isEditing(): bool
    {
        return $this->game !== null && $this->game->exists;
    }

    public function render()
    {
        return view('livewire.games.game-form', [
            'statuses' => PlayingStatus::cases(),
            'ownershipStatuses' => OwnershipStatus::cases(),
            'isEditing' => $this->isEditing(),
        ])->layout('layouts.app');
    }
}
