<?php

declare(strict_types=1);

use App\Enums\BoardGameStatus;
use App\Enums\CollectionStatus;
use App\Enums\ListeningStatus;
use App\Enums\OwnershipStatus;
use App\Enums\PlayingStatus;

it('PlayingStatus exposes labels, colors and backing values', function (): void {
    expect(PlayingStatus::cases())->toHaveCount(5);
    expect(PlayingStatus::Backlog->label())->toBe('Backlog');
    expect(PlayingStatus::Mastered->label())->toBe('Mastered');
    expect(PlayingStatus::Playing->color())->toBe('yellow');
    expect(PlayingStatus::Completed->color())->toBe('green');
    expect(PlayingStatus::from('backlog'))->toBe(PlayingStatus::Backlog);
});

it('BoardGameStatus exposes labels, colors and backing values', function (): void {
    expect(BoardGameStatus::cases())->toHaveCount(5);
    expect(BoardGameStatus::WantToPlay->label())->toBe('Want to Play');
    expect(BoardGameStatus::PreviouslyOwned->label())->toBe('Previously Owned');
    expect(BoardGameStatus::ForTrade->color())->toBe('for-trade');
    expect(BoardGameStatus::from('for_trade'))->toBe(BoardGameStatus::ForTrade);
});

it('CollectionStatus exposes labels, colors and backing values', function (): void {
    expect(CollectionStatus::cases())->toHaveCount(4);
    expect(CollectionStatus::Listening->label())->toBe('Listening');
    expect(CollectionStatus::Listened->color())->toBe('green');
    expect(CollectionStatus::from('wishlist'))->toBe(CollectionStatus::Wishlist);
});

it('OwnershipStatus exposes labels, colors and backing values', function (): void {
    expect(OwnershipStatus::cases())->toHaveCount(5);
    expect(OwnershipStatus::OnEmulator->label())->toBe('On Emulator');
    expect(OwnershipStatus::NotOwned->color())->toBe('gray');
    expect(OwnershipStatus::from('previously_owned'))->toBe(OwnershipStatus::PreviouslyOwned);
});

it('ListeningStatus exposes labels, colors and backing values', function (): void {
    expect(ListeningStatus::cases())->toHaveCount(4);
    expect(ListeningStatus::WantToGo->label())->toBe('Want to Go');
    expect(ListeningStatus::Attended->color())->toBe('green');
    expect(ListeningStatus::from('missed'))->toBe(ListeningStatus::Missed);
});

it('every status enum returns a non-empty label and color for all cases', function (): void {
    $enums = [
        PlayingStatus::class,
        BoardGameStatus::class,
        CollectionStatus::class,
        OwnershipStatus::class,
        ListeningStatus::class,
    ];

    foreach ($enums as $enum) {
        foreach ($enum::cases() as $case) {
            expect($case->label())->toBeString()->not->toBe('');
            expect($case->color())->toBeString()->not->toBe('');
        }
    }
});
