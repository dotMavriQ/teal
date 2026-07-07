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

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->other = User::factory()->create();
});

/**
 * relation name => model class. Each media model is user-scoped, so the relation
 * must return only the owner's records.
 */
dataset('relations', [
    'books' => ['books', Book::class],
    'movies' => ['movies', Movie::class],
    'anime' => ['anime', Anime::class],
    'comics' => ['comics', Comic::class],
    'shows' => ['shows', Show::class],
    'games' => ['games', Game::class],
    'boardGames' => ['boardGames', BoardGame::class],
    'albums' => ['albums', Album::class],
    'concerts' => ['concerts', Concert::class],
]);

it('returns only the owning user\'s records for each media relation', function (string $relation, string $model): void {
    $model::factory()->count(2)->create(['user_id' => $this->user->id]);
    $model::factory()->create(['user_id' => $this->other->id]);

    $related = $this->user->{$relation};

    expect($related)->toHaveCount(2);
    expect($related->pluck('user_id')->unique()->all())->toBe([$this->user->id]);
})->with('relations');

it('cascade deletes all media records when the user is deleted', function (): void {
    Book::factory()->create(['user_id' => $this->user->id]);
    Movie::factory()->create(['user_id' => $this->user->id]);
    Concert::factory()->create(['user_id' => $this->user->id]);

    $otherBook = Book::factory()->create(['user_id' => $this->other->id]);

    $this->user->delete();

    expect(Book::whereKey($otherBook->id)->exists())->toBeTrue();
    expect(Book::where('user_id', $this->user->id)->count())->toBe(0);
    expect(Movie::where('user_id', $this->user->id)->count())->toBe(0);
    expect(Concert::where('user_id', $this->user->id)->count())->toBe(0);
});
