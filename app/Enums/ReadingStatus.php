<?php

declare(strict_types=1);

namespace App\Enums;

enum ReadingStatus: string
{
    case WantToRead = 'want_to_read';
    case Reading = 'reading';
    case Read = 'read';

    public function label(): string
    {
        return match ($this) {
            self::WantToRead => 'Want to Read',
            self::Reading => 'Currently Reading',
            self::Read => 'Read',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WantToRead => 'blue',
            self::Reading => 'yellow',
            self::Read => 'green',
        };
    }
}
