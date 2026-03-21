<?php

declare(strict_types=1);

namespace App\Enums;

enum PlayingStatus: string
{
    case WantToPlay = 'want_to_play';
    case Playing = 'playing';
    case Played = 'played';

    public function label(): string
    {
        return match ($this) {
            self::WantToPlay => 'Want to Play',
            self::Playing => 'Playing',
            self::Played => 'Played',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WantToPlay => 'purple',
            self::Playing => 'yellow',
            self::Played => 'green',
        };
    }
}
