<?php

declare(strict_types=1);

use App\Enums\ReadingStatus;
use App\Models\Book;
use App\Models\User;
use App\Services\GoodReadsImportService;

beforeEach(function () {
    $this->service = new GoodReadsImportService;
    $this->user = User::factory()->create();
});

it('parses a valid GoodReads CSV', function () {
    $csv = implode("\n", [
        'Title,Author,ISBN,ISBN13,My Rating,Exclusive Shelf,Number of Pages,Year Published,Book Id',
        'The Great Gatsby,F. Scott Fitzgerald,0743273567,9780743273565,4,read,180,1925,4671',
        'Dune,Frank Herbert,0441172717,9780441172719,5,currently-reading,688,1965,234225',
    ]);

    $books = $this->service->parseCSV($csv);

    expect($books)->toHaveCount(2);
    expect($books[0]['title'])->toBe('The Great Gatsby');
    expect($books[0]['author'])->toBe('F. Scott Fitzgerald');
    expect($books[0]['status'])->toBe(ReadingStatus::Read);
    expect($books[0]['rating'])->toBe(4);
    expect($books[1]['title'])->toBe('Dune');
    expect($books[1]['status'])->toBe(ReadingStatus::Reading);
});

it('maps shelf names to correct statuses', function () {
    $csv = implode("\n", [
        'Title,Author,ISBN,ISBN13,My Rating,Exclusive Shelf,Number of Pages,Year Published,Book Id',
        'Book A,Author A,,,0,to-read,100,2020,1',
        'Book B,Author B,,,3,read,200,2021,2',
        'Book C,Author C,,,0,currently-reading,150,2022,3',
    ]);

    $books = $this->service->parseCSV($csv);

    expect($books[0]['status'])->toBe(ReadingStatus::WantToRead);
    expect($books[1]['status'])->toBe(ReadingStatus::Read);
    expect($books[2]['status'])->toBe(ReadingStatus::Reading);
});

it('imports books into the database', function () {
    $csv = implode("\n", [
        'Title,Author,ISBN,ISBN13,My Rating,Exclusive Shelf,Number of Pages,Year Published,Book Id',
        'Test Book,Test Author,1234567890,9781234567890,3,read,200,2020,99999',
    ]);

    $books = $this->service->parseCSV($csv);
    $result = $this->service->importBooks($this->user, $books);

    expect($result['imported'])->toBe(1);
    expect($result['skipped'])->toBe(0);
    $this->assertDatabaseHas('books', [
        'user_id' => $this->user->id,
        'title' => 'Test Book',
        'author' => 'Test Author',
        'goodreads_id' => '99999',
    ]);
});

it('detects duplicates by goodreads_id', function () {
    Book::factory()->create([
        'user_id' => $this->user->id,
        'goodreads_id' => '99999',
    ]);

    $csv = implode("\n", [
        'Title,Author,ISBN,ISBN13,My Rating,Exclusive Shelf,Number of Pages,Year Published,Book Id',
        'Duplicate Book,Author,,,3,read,200,2020,99999',
    ]);

    $books = $this->service->parseCSV($csv);
    $result = $this->service->importBooks($this->user, $books);

    expect($result['imported'])->toBe(0);
    expect($result['skipped'])->toBe(1);
});

it('detects duplicates by isbn13', function () {
    Book::factory()->create([
        'user_id' => $this->user->id,
        'isbn13' => '9781234567890',
    ]);

    $csv = implode("\n", [
        'Title,Author,ISBN,ISBN13,My Rating,Exclusive Shelf,Number of Pages,Year Published,Book Id',
        'Another Book,Author,,9781234567890,3,read,200,2020,',
    ]);

    $books = $this->service->parseCSV($csv);
    $result = $this->service->importBooks($this->user, $books);

    expect($result['imported'])->toBe(0);
    expect($result['skipped'])->toBe(1);
});

it('skips empty rows', function () {
    $csv = "Title,Author,ISBN,ISBN13,My Rating,Exclusive Shelf,Number of Pages,Year Published,Book Id\n\n\n";

    $books = $this->service->parseCSV($csv);

    expect($books)->toHaveCount(0);
});

it('handles zero rating as null', function () {
    $csv = implode("\n", [
        'Title,Author,ISBN,ISBN13,My Rating,Exclusive Shelf,Number of Pages,Year Published,Book Id',
        'Unrated Book,Author,,,0,to-read,100,2020,1',
    ]);

    $books = $this->service->parseCSV($csv);

    expect($books[0]['rating'])->toBeNull();
});
