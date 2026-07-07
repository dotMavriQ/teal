<?php

declare(strict_types=1);

use App\Models\Album;
use App\Models\Anime;
use App\Models\BoardGame;
use App\Models\Book;
use App\Models\Comic;
use App\Models\Concert;
use App\Models\Game;
use App\Models\Movie;
use App\Models\Show;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Every media policy authorises view/update/delete only when the acting user
 * owns the record, and always allows viewAny/create. This dataset drives the
 * same assertions across all nine.
 *
 * @return list<array{class-string}>
 */
dataset('models', [
    'Book' => [Book::class],
    'Movie' => [Movie::class],
    'Anime' => [Anime::class],
    'Comic' => [Comic::class],
    'Show' => [Show::class],
    'Game' => [Game::class],
    'BoardGame' => [BoardGame::class],
    'Album' => [Album::class],
    'Concert' => [Concert::class],
]);

it('lets the owner view, update and delete their record', function (string $model): void {
    $owner = User::factory()->create();
    $record = $model::factory()->create(['user_id' => $owner->id]);

    $gate = Gate::forUser($owner);

    expect($gate->allows('view', $record))->toBeTrue();
    expect($gate->allows('update', $record))->toBeTrue();
    expect($gate->allows('delete', $record))->toBeTrue();
})->with('models');

it('forbids a non-owner from viewing, updating or deleting the record', function (string $model): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $record = $model::factory()->create(['user_id' => $owner->id]);

    $gate = Gate::forUser($stranger);

    expect($gate->allows('view', $record))->toBeFalse();
    expect($gate->allows('update', $record))->toBeFalse();
    expect($gate->allows('delete', $record))->toBeFalse();
})->with('models');

it('always allows viewAny and create for any authenticated user', function (string $model): void {
    $user = User::factory()->create();
    $gate = Gate::forUser($user);

    expect($gate->allows('viewAny', $model))->toBeTrue();
    expect($gate->allows('create', $model))->toBeTrue();
})->with('models');
