<?php

declare(strict_types=1);

use App\Enums\ReadingStatus;
use App\Models\Book;
use App\Models\User;
use App\Services\JsonImportService;

beforeEach(function () {
    $this->service = new JsonImportService;
    $this->user = User::factory()->create();
});

it('parses valid JSON book data', function () {
    $json = json_encode([
        [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn13' => '9781234567890',
            'rating' => 4,
            'shelves' => 'read',
            'num_pages' => 300,
        ],
    ]);

    $books = $this->service->parseJson($json);

    expect($books)->toHaveCount(1);
    expect($books->first()['title'])->toBe('Test Book');
    expect($books->first()['author'])->toBe('Test Author');
    expect($books->first()['status'])->toBe(ReadingStatus::Read);
    expect($books->first()['rating'])->toBe(4);
});

it('maps shelves to correct statuses', function () {
    $json = json_encode([
        ['title' => 'A', 'author' => 'X', 'shelves' => 'to-read'],
        ['title' => 'B', 'author' => 'Y', 'shelves' => 'currently-reading'],
        ['title' => 'C', 'author' => 'Z', 'shelves' => 'read'],
    ]);

    $books = $this->service->parseJson($json);

    expect($books[0]['status'])->toBe(ReadingStatus::WantToRead);
    expect($books[1]['status'])->toBe(ReadingStatus::Reading);
    expect($books[2]['status'])->toBe(ReadingStatus::Read);
});

it('imports books into the database', function () {
    $json = json_encode([
        [
            'title' => 'Imported Book',
            'author' => 'Imported Author',
            'isbn13' => '9781234567890',
            'rating' => 3,
            'shelves' => 'read',
        ],
    ]);

    $books = $this->service->parseJson($json);
    $result = $this->service->importBooks($this->user, $books);

    expect($result['imported'])->toBe(1);
    $this->assertDatabaseHas('books', [
        'user_id' => $this->user->id,
        'title' => 'Imported Book',
    ]);
});

it('detects duplicates by isbn13', function () {
    Book::factory()->create([
        'user_id' => $this->user->id,
        'isbn13' => '9781234567890',
    ]);

    $json = json_encode([
        [
            'title' => 'Duplicate',
            'author' => 'Author',
            'isbn13' => '9781234567890',
            'shelves' => 'read',
        ],
    ]);

    $books = $this->service->parseJson($json);
    $result = $this->service->importBooks($this->user, $books);

    expect($result['imported'])->toBe(0);
    expect($result['skipped'])->toBe(1);
});

it('detects duplicates by title and author', function () {
    Book::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Same Title',
        'author' => 'Same Author',
        'isbn' => null,
        'isbn13' => null,
    ]);

    $json = json_encode([
        [
            'title' => 'Same Title',
            'author' => 'Same Author',
            'shelves' => 'read',
        ],
    ]);

    $books = $this->service->parseJson($json);
    $result = $this->service->importBooks($this->user, $books);

    expect($result['imported'])->toBe(0);
    expect($result['skipped'])->toBe(1);
});

it('throws on invalid JSON', function () {
    $this->service->parseJson('not json');
})->throws(\InvalidArgumentException::class);

it('creates shelves from custom shelf data', function () {
    $json = json_encode([
        [
            'title' => 'Book With Shelf',
            'author' => 'Author',
            'shelves' => 'read, sci-fi, favorites',
            'rating' => 5,
        ],
    ]);

    $books = $this->service->parseJson($json);
    $result = $this->service->importBooks($this->user, $books);

    expect($result['imported'])->toBe(1);
    $this->assertDatabaseHas('shelves', [
        'user_id' => $this->user->id,
        'name' => 'sci-fi',
    ]);
    $this->assertDatabaseHas('shelves', [
        'user_id' => $this->user->id,
        'name' => 'favorites',
    ]);
});
