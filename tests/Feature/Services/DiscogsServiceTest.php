<?php

declare(strict_types=1);

use App\Services\DiscogsService;
use App\Services\Saloon\Discogs\Requests\SearchReleases;
use Illuminate\Support\Facades\Cache;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(fn () => Cache::flush());
afterEach(fn () => MockClient::destroyGlobal());

it('normalizes, merges and de-duplicates search results', function (): void {
    MockClient::global([
        SearchReleases::class => MockResponse::make([
            'results' => [
                [
                    'id' => 1,
                    'title' => 'OK Computer',
                    'year' => '1997',
                    'format' => ['Vinyl', 'LP'],
                    'label' => ['XL Recordings', 'Parlophone'],
                    'country' => 'UK',
                    'type' => 'master',
                ],
                ['id' => 2],   // minimal row exercises the defaults
                ['id' => 1],   // duplicate id within the same response
            ],
        ], 200),
    ]);

    // search() sends an artist-scoped and a general request (same class), so the
    // mock answers both; de-dup by id must collapse them to two unique rows.
    $out = app(DiscogsService::class)->search('radiohead');

    expect($out)->toHaveCount(2);

    expect($out[0]['id'])->toBe(1);
    expect($out[0]['title'])->toBe('OK Computer');
    expect($out[0]['format'])->toBe('Vinyl, LP');
    expect($out[0]['label'])->toBe('XL Recordings');
    expect($out[0]['country'])->toBe('UK');

    expect($out[1]['title'])->toBe('Unknown');
    expect($out[1]['master_id'])->toBe(2); // falls back to id when master_id is absent
});

it('returns an empty list when the search fails', function (): void {
    MockClient::global([
        SearchReleases::class => MockResponse::make(['message' => 'error'], 500),
    ]);

    expect(app(DiscogsService::class)->search('broken'))->toBe([]);
});

it('skips rows without a scalar id', function (): void {
    MockClient::global([
        SearchReleases::class => MockResponse::make([
            'results' => [
                ['title' => 'No id here'],
                ['id' => 7, 'title' => 'Kept'],
            ],
        ], 200),
    ]);

    $out = app(DiscogsService::class)->search('mix');

    expect($out)->toHaveCount(1);
    expect($out[0]['id'])->toBe(7);
});
