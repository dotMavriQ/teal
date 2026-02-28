<?php

declare(strict_types=1);

use App\Enums\ReadingStatus;

it('has correct labels for all cases', function () {
    expect(ReadingStatus::WantToRead->label())->toBe('Want to Read');
    expect(ReadingStatus::Reading->label())->toBe('Currently Reading');
    expect(ReadingStatus::Read->label())->toBe('Read');
});

it('has correct colors for all cases', function () {
    expect(ReadingStatus::WantToRead->color())->toBe('blue');
    expect(ReadingStatus::Reading->color())->toBe('yellow');
    expect(ReadingStatus::Read->color())->toBe('green');
});

it('has correct backing values', function () {
    expect(ReadingStatus::WantToRead->value)->toBe('want_to_read');
    expect(ReadingStatus::Reading->value)->toBe('reading');
    expect(ReadingStatus::Read->value)->toBe('read');
});

it('has exactly three cases', function () {
    expect(ReadingStatus::cases())->toHaveCount(3);
});
