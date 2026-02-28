<?php

declare(strict_types=1);

use App\Enums\WatchingStatus;

it('has correct labels for all cases', function () {
    expect(WatchingStatus::Watchlist->label())->toBe('Watchlist');
    expect(WatchingStatus::Watching->label())->toBe('Watching');
    expect(WatchingStatus::Watched->label())->toBe('Watched');
});

it('has correct colors for all cases', function () {
    expect(WatchingStatus::Watchlist->color())->toBe('purple');
    expect(WatchingStatus::Watching->color())->toBe('yellow');
    expect(WatchingStatus::Watched->color())->toBe('green');
});

it('has correct backing values', function () {
    expect(WatchingStatus::Watchlist->value)->toBe('watchlist');
    expect(WatchingStatus::Watching->value)->toBe('watching');
    expect(WatchingStatus::Watched->value)->toBe('watched');
});

it('has exactly three cases', function () {
    expect(WatchingStatus::cases())->toHaveCount(3);
});
