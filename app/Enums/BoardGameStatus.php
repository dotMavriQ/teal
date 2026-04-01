<?php

declare(strict_types=1);

namespace App\Enums;

enum BoardGameStatus: string
{
    case Owned = 'owned';
    case WantToPlay = 'want_to_play';
    case Wishlist = 'wishlist';
    case ForTrade = 'for_trade';
    case PreviouslyOwned = 'previously_owned';

    public function label(): string
    {
        return match ($this) {
            self::Owned => 'Owned',
            self::WantToPlay => 'Want to Play',
            self::Wishlist => 'Wishlist',
            self::ForTrade => 'For Trade',
            self::PreviouslyOwned => 'Previously Owned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Owned => 'owned',
            self::WantToPlay => 'want-to-play',
            self::Wishlist => 'wishlist',
            self::ForTrade => 'for-trade',
            self::PreviouslyOwned => 'previously-owned',
        };
    }
}
