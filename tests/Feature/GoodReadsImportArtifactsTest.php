<?php

declare(strict_types=1);

use App\Services\GoodReadsImportService;
use App\Enums\ReadingStatus;

beforeEach(function () {
    $this->service = new GoodReadsImportService;
});

it('cleans ISBN with Excel-style artifacts', function () {
    $csv = implode("
", [
        'Title,Author,ISBN,ISBN13,My Rating,Exclusive Shelf,Number of Pages,Year Published,Book Id',
        'Excel Book,Author,="0743273567",="9780743273565",4,read,180,1925,4671',
    ]);

    $books = $this->service->parseCSV($csv);

    expect($books)->toHaveCount(1);
    expect($books[0]['isbn'])->toBe('0743273567');
    expect($books[0]['isbn13'])->toBe('9780743273565');
});

it('handles accent marks in titles (UTF-8)', function () {
    $csv = implode("
", [
        'Title,Author,ISBN,ISBN13,My Rating,Exclusive Shelf,Number of Pages,Year Published,Book Id',
        'Jag vill inte förstå,Author,,,0,read,100,2023,1',
        'Žižek in the Clinic,Author,,,0,read,100,2023,2',
    ]);

    $books = $this->service->parseCSV($csv);

    expect($books[0]['title'])->toBe('Jag vill inte förstå');
    expect($books[1]['title'])->toBe('Žižek in the Clinic');
});
