<?php

declare(strict_types=1);

namespace App\Enums;

enum WatchingStatus: string
{
    case Watchlist = 'watchlist';
    case Watching = 'watching';
    case Watched = 'watched';

    public function label(): string
    {
        return match ($this) {
            self::Watchlist => 'Watchlist',
            self::Watching => 'Watching',
            self::Watched => 'Watched',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Watchlist => 'purple',
            self::Watching => 'yellow',
            self::Watched => 'green',
        };
    }
}
