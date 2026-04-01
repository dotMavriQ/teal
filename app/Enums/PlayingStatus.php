<?php

declare(strict_types=1);

namespace App\Enums;

enum PlayingStatus: string
{
    case Backlog = 'backlog';
    case Playing = 'playing';
    case Shelved = 'shelved';
    case Completed = 'completed';
    case Mastered = 'mastered';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Backlog',
            self::Playing => 'Playing',
            self::Shelved => 'Shelved',
            self::Completed => 'Completed',
            self::Mastered => 'Mastered',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Backlog => 'gray',
            self::Playing => 'yellow',
            self::Shelved => 'orange',
            self::Completed => 'green',
            self::Mastered => 'purple',
        };
    }
}
