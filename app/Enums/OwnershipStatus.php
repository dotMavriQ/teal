<?php

declare(strict_types=1);

namespace App\Enums;

enum OwnershipStatus: string
{
    case Owned = 'owned';
    case PreviouslyOwned = 'previously_owned';
    case Borrowed = 'borrowed';
    case OnEmulator = 'on_emulator';
    case NotOwned = 'not_owned';

    public function label(): string
    {
        return match ($this) {
            self::Owned => 'Owned',
            self::PreviouslyOwned => 'Previously Owned',
            self::Borrowed => 'Borrowed',
            self::OnEmulator => 'On Emulator',
            self::NotOwned => 'Not Owned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Owned => 'green',
            self::PreviouslyOwned => 'yellow',
            self::Borrowed => 'blue',
            self::OnEmulator => 'purple',
            self::NotOwned => 'gray',
        };
    }
}
