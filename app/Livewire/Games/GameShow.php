<?php

declare(strict_types=1);

namespace App\Livewire\Games;

use App\Models\Game;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class GameShow extends Component
{
    use AuthorizesRequests;

    public Game $game;

    public function mount(Game $game): void
    {
        $this->authorize('view', $game);
        $this->game = $game;
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->game);

        $newRating = $this->game->rating === $rating ? null : $rating;
        $this->game->update(['rating' => $newRating]);
    }

    public function deleteGame(): void
    {
        $this->authorize('delete', $this->game);

        $this->game->delete();

        session()->flash('message', 'Game deleted successfully.');
        $this->redirect(route('games.index'));
    }

    public static function platformMeta(string $platform): array
    {
        $lower = strtolower($platform);

        if (str_contains($lower, 'game boy color') || str_contains($lower, 'gbc')) {
            return ['key' => 'nintendo', 'logo' => 'gameboy_color.svg'];
        }

        if (str_contains($lower, 'game boy advance') || str_contains($lower, 'gba')) {
            return ['key' => 'nintendo', 'logo' => 'gameboy.svg'];
        }

        if (str_contains($lower, 'game boy') || str_contains($lower, 'gameboy')) {
            return ['key' => 'nintendo', 'logo' => 'gameboy.svg'];
        }

        if (str_contains($lower, 'switch')) {
            return ['key' => 'nintendo', 'logo' => 'nintendo_switch.svg'];
        }

        if (str_contains($lower, 'nintendo entertainment system') || $lower === 'nes') {
            return ['key' => 'nintendo', 'logo' => 'nes.svg'];
        }

        if (str_contains($lower, 'nintendo 64') || $lower === 'n64') {
            return ['key' => 'nintendo', 'logo' => 'n64.svg'];
        }

        if (str_contains($lower, 'super nintendo') || str_contains($lower, 'snes')) {
            return ['key' => 'nintendo', 'logo' => 'nintendo_switch.svg'];
        }

        if (str_contains($lower, 'nintendo') || str_contains($lower, 'wii') || str_contains($lower, 'gamecube') || str_contains($lower, '3ds')) {
            return ['key' => 'nintendo', 'logo' => 'nintendo_switch.svg'];
        }

        if (str_contains($lower, 'steam')) {
            return ['key' => 'steam', 'logo' => 'steam.svg'];
        }

        if (str_contains($lower, 'gog')) {
            return ['key' => 'gog', 'logo' => 'gog.svg'];
        }

        if (str_contains($lower, 'playstation 2') || str_contains($lower, 'ps2')) {
            return ['key' => 'playstation', 'logo' => 'ps2.svg'];
        }

        if (str_contains($lower, 'playstation') || str_contains($lower, 'ps vita') || str_contains($lower, 'psp')) {
            $logo = 'playstation4.svg';
            if (str_contains($lower, '5')) {
                $logo = 'playstation5.svg';
            }

            return ['key' => 'playstation', 'logo' => $logo];
        }

        if (str_contains($lower, 'xbox')) {
            $logo = str_contains($lower, 'series') ? 'xbox_series.svg' : 'xbox_one.svg';

            return ['key' => 'xbox', 'logo' => $logo];
        }

        if (str_contains($lower, 'pc') || str_contains($lower, 'windows') || str_contains($lower, 'linux') || str_contains($lower, 'mac')) {
            return ['key' => 'pc', 'logo' => 'windows.svg'];
        }

        return ['key' => 'default', 'logo' => null];
    }

    public static function shortPlatformName(string $platform): string
    {
        return match ($platform) {
            'Nintendo Entertainment System' => 'NES',
            'Super Nintendo Entertainment System' => 'SNES',
            'Nintendo 64' => 'N64',
            'PlayStation 2' => 'PS2',
            'Game Boy Advance' => 'GBA',
            'Game Boy Color' => 'GBC',
            'Game Boy' => 'GB',
            'PC (Steam)' => 'Steam',
            default => $platform,
        };
    }

    public function render()
    {
        return view('livewire.games.game-show')
            ->layout('layouts.app');
    }
}
