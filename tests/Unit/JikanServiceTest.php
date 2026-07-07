<?php

declare(strict_types=1);

use App\Services\JikanService;

/*
|--------------------------------------------------------------------------
| JikanService::normalizeData()
|--------------------------------------------------------------------------
| Pure data-transformation tests over the Jikan (MAL) API payload shape.
| No HTTP/DB — this is where the normalization bugs live.
*/

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function jikanPayload(array $overrides = []): array
{
    return array_replace([
        'mal_id' => 5114,
        'url' => 'https://myanimelist.net/anime/5114',
        'title' => 'Fullmetal Alchemist: Brotherhood',
        'title_japanese' => '鋼の錬金術師 FULLMETAL ALCHEMIST',
        'synopsis' => 'After a failed attempt…',
        'type' => 'TV',
        'episodes' => 64,
        'duration' => '24 min per ep',
        'year' => 2009,
        'score' => 9.1,
        'images' => ['jpg' => [
            'image_url' => 'https://cdn/small.jpg',
            'large_image_url' => 'https://cdn/large.jpg',
        ]],
        'genres' => [['name' => 'Action'], ['name' => 'Adventure']],
        'themes' => [['name' => 'Military']],
        'studios' => [['name' => 'Bones']],
    ], $overrides);
}

it('maps the core fields from a full payload', function () {
    $out = (new JikanService)->normalizeData(jikanPayload());

    expect($out)->toMatchArray([
        'title' => 'Fullmetal Alchemist: Brotherhood',
        'original_title' => '鋼の錬金術師 FULLMETAL ALCHEMIST',
        'description' => 'After a failed attempt…',
        'poster_url' => 'https://cdn/large.jpg',
        'year' => 2009,
        'episodes_total' => 64,
        'runtime_minutes' => 24,
        'genres' => 'Action, Adventure, Military',
        'studios' => 'Bones',
        'media_type' => 'TV',
        'mal_id' => 5114,
        'mal_score' => 9.1,
        'mal_url' => 'https://myanimelist.net/anime/5114',
    ]);
});

it('falls back to aired.from for the year when year is missing', function () {
    $out = (new JikanService)->normalizeData(jikanPayload([
        'year' => null,
        'aired' => ['from' => '2011-04-06T00:00:00+00:00'],
    ]));

    expect($out['year'])->toBe(2011);
});

it('prefers large_image_url but falls back to image_url', function () {
    $out = (new JikanService)->normalizeData(jikanPayload([
        'images' => ['jpg' => ['image_url' => 'https://cdn/only-small.jpg']],
    ]));

    expect($out['poster_url'])->toBe('https://cdn/only-small.jpg');
});

it('parses hours and minutes in duration', function () {
    expect((new JikanService)->normalizeData(jikanPayload(['duration' => '1 hr 40 min']))['runtime_minutes'])
        ->toBe(100);
});

it('returns null runtime for "Unknown" duration', function () {
    expect((new JikanService)->normalizeData(jikanPayload(['duration' => 'Unknown']))['runtime_minutes'])
        ->toBeNull();
});

it('deduplicates names across genres and themes', function () {
    $out = (new JikanService)->normalizeData(jikanPayload([
        'genres' => [['name' => 'Action'], ['name' => 'Drama']],
        'themes' => [['name' => 'Action'], ['name' => 'Military']],
    ]));

    expect($out['genres'])->toBe('Action, Drama, Military');
});

it('passes through known media types and unknown ones alike', function () {
    expect((new JikanService)->normalizeData(jikanPayload(['type' => 'OVA']))['media_type'])->toBe('OVA')
        ->and((new JikanService)->normalizeData(jikanPayload(['type' => 'PV']))['media_type'])->toBe('PV');
});

it('coerces empty/missing fields to null without erroring', function () {
    $out = (new JikanService)->normalizeData([]);

    expect($out['title'])->toBeNull()
        ->and($out['year'])->toBeNull()
        ->and($out['poster_url'])->toBeNull()
        ->and($out['genres'])->toBeNull()
        ->and($out['studios'])->toBeNull()
        ->and($out['runtime_minutes'])->toBeNull()
        ->and($out['media_type'])->toBeNull();
});
