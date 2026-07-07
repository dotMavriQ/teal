<?php

declare(strict_types=1);

use App\Services\Saloon\Tmdb\Requests\GetMovieDetails;
use App\Services\Saloon\Tmdb\Requests\GetTvDetails;
use App\Services\TmdbService;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

/*
|--------------------------------------------------------------------------
| TmdbService — normalizeData() via mocked movie/TV responses
|--------------------------------------------------------------------------
| normalizeData() handles both the movie and TV payload shapes; exercised
| through the public fetch methods with faked connector responses.
*/

uses(Tests\TestCase::class);

it('normalizes a movie payload', function () {
    Saloon::fake([GetMovieDetails::class => MockResponse::make([
        'title' => 'Inception',
        'original_title' => 'Inception',
        'release_date' => '2010-07-16',
        'imdb_id' => 'tt1375666',
        'overview' => 'A thief who steals corporate secrets…',
        'poster_path' => '/poster.jpg',
        'runtime' => 148,
        'genres' => [['name' => 'Action'], ['name' => 'Science Fiction']],
        'credits' => ['crew' => [
            ['job' => 'Director', 'name' => 'Christopher Nolan'],
            ['job' => 'Writer', 'name' => 'Someone Else'],
        ]],
    ])]);

    expect((new TmdbService)->fetchMovieDetails(27205))->toMatchArray([
        'title' => 'Inception',
        'original_title' => 'Inception',
        'year' => 2010,
        'imdb_id' => 'tt1375666',
        'description' => 'A thief who steals corporate secrets…',
        'poster_url' => 'https://image.tmdb.org/t/p/w500/poster.jpg',
        'runtime_minutes' => 148,
        'release_date' => '2010-07-16',
        'genres' => 'Action, Sci-Fi',            // "Science Fiction" mapped to "Sci-Fi"
        'director' => 'Christopher Nolan',        // only crew with job=Director
    ]);
});

it('normalizes a TV payload (name/first_air_date/episode_run_time/external_ids)', function () {
    Saloon::fake([GetTvDetails::class => MockResponse::make([
        'name' => 'The Expanse',
        'original_name' => 'The Expanse',
        'first_air_date' => '2015-12-14',
        'episode_run_time' => [60],
        'external_ids' => ['imdb_id' => 'tt3230854'],
        'overview' => 'In the 24th century…',
        'poster_path' => '/exp.jpg',
        'genres' => [['name' => 'Sci-Fi & Fantasy'], ['name' => 'Drama']],
    ])]);

    expect((new TmdbService)->fetchTVDetails(63639))->toMatchArray([
        'title' => 'The Expanse',                 // falls back from `name`
        'original_title' => 'The Expanse',
        'year' => 2015,                           // from first_air_date
        'imdb_id' => 'tt3230854',                 // from external_ids fallback
        'runtime_minutes' => 60,                  // from episode_run_time[0]
        'release_date' => '2015-12-14',
        'genres' => 'Sci-Fi, Fantasy, Drama',     // "Sci-Fi & Fantasy" splits into two
        'director' => null,                       // no credits
    ]);
});

it('returns null when the movie request fails', function () {
    Saloon::fake([GetMovieDetails::class => MockResponse::make('', 404)]);

    expect((new TmdbService)->fetchMovieDetails(0))->toBeNull();
});
