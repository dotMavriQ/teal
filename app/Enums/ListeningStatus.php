<?php

declare(strict_types=1);

namespace App\Enums;

enum ListeningStatus: string
{
    case WantToGo = 'want_to_go';
    case Going = 'going';
    case Attended = 'attended';
    case Missed = 'missed';

    public function label(): string
    {
        return match ($this) {
            self::WantToGo => 'Want to Go',
            self::Going => 'Going',
            self::Attended => 'Attended',
            self::Missed => 'Missed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WantToGo => 'blue',
            self::Going => 'yellow',
            self::Attended => 'green',
            self::Missed => 'gray',
        };
    }
}
