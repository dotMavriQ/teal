<?php

declare(strict_types=1);

use App\Services\Saloon\SetlistFm\Requests\GetArtistSetlists;
use App\Services\Saloon\SetlistFm\Requests\SearchArtists;
use App\Services\SetlistFmService;
use Illuminate\Support\Facades\Cache;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(fn () => Cache::flush());
afterEach(fn () => MockClient::destroyGlobal());

it('normalizes artist search results and defaults a missing name', function (): void {
    MockClient::global([
        SearchArtists::class => MockResponse::make([
            'artist' => [
                ['mbid' => 'abc', 'name' => 'Radiohead', 'sortName' => 'Radiohead', 'disambiguation' => 'UK band'],
                ['mbid' => 'def', 'sortName' => 'Nameless'],
            ],
        ], 200),
    ]);

    $out = app(SetlistFmService::class)->searchArtists('radiohead');

    expect($out)->toHaveCount(2);
    expect($out[0])->toBe([
        'mbid' => 'abc',
        'name' => 'Radiohead',
        'sort_name' => 'Radiohead',
        'disambiguation' => 'UK band',
    ]);
    expect($out[1]['name'])->toBe('Unknown');
});

it('caps artist results at 20', function (): void {
    $artists = array_map(fn (int $i): array => ['mbid' => "m$i", 'name' => "Artist $i"], range(1, 25));

    MockClient::global([
        SearchArtists::class => MockResponse::make(['artist' => $artists], 200),
    ]);

    expect(app(SetlistFmService::class)->searchArtists('many'))->toHaveCount(20);
});

it('returns an empty list when the artist search fails', function (): void {
    MockClient::global([
        SearchArtists::class => MockResponse::make(['error' => 'boom'], 500),
    ]);

    expect(app(SetlistFmService::class)->searchArtists('broken'))->toBe([]);
});

it('normalizes artist setlists with totals and pagination', function (): void {
    MockClient::global([
        GetArtistSetlists::class => MockResponse::make([
            'setlist' => [],
            'total' => 42,
            'page' => 2,
            'itemsPerPage' => 20,
        ], 200),
    ]);

    $result = app(SetlistFmService::class)->getArtistSetlists('mbid-123', 2);

    expect($result['total'])->toBe(42);
    expect($result['page'])->toBe(2);
    expect($result['items_per_page'])->toBe(20);
    expect($result['setlists'])->toBe([]);
});

it('returns an empty setlist payload when the setlist request fails', function (): void {
    MockClient::global([
        GetArtistSetlists::class => MockResponse::make([], 404),
    ]);

    expect(app(SetlistFmService::class)->getArtistSetlists('mbid-missing'))
        ->toBe(['setlists' => [], 'total' => 0]);
});
