<?php

declare(strict_types=1);

namespace App\Enums;

enum CollectionStatus: string
{
    case Wishlist = 'wishlist';
    case Listening = 'listening';
    case Listened = 'listened';
    case Shelved = 'shelved';

    public function label(): string
    {
        return match ($this) {
            self::Wishlist => 'Wishlist',
            self::Listening => 'Listening',
            self::Listened => 'Listened',
            self::Shelved => 'Shelved',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Wishlist => 'blue',
            self::Listening => 'yellow',
            self::Listened => 'green',
            self::Shelved => 'gray',
        };
    }
}
