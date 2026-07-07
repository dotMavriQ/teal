<?php

declare(strict_types=1);

use App\Livewire\Movies\MovieTmdbSearch;
use App\Models\User;
use App\Services\TmdbService;
use Livewire\Livewire;

/**
 * Fake TMDB service so the wizard's URL/rehydration behaviour can be tested
 * without hitting the network. The component resolves TmdbService via app(),
 * so binding an instance into the container is enough.
 */
class FakeTmdbService extends TmdbService
{
    public function searchMulti(string $query, int $page = 1): array
    {
        return [
            'results' => [
                ['tmdb_id' => 438631, 'media_type' => 'movie', 'title' => 'Dune', 'year' => 2021, 'poster_url' => null],
                ['tmdb_id' => 1399, 'media_type' => 'tv', 'title' => 'Game of Thrones', 'year' => 2011, 'poster_url' => null],
            ],
            'total_pages' => 1,
        ];
    }

    public function fetchMovieDetails(int $tmdbId): ?array
    {
        return [
            'title' => 'Dune',
            'original_title' => 'Dune',
            'director' => 'Denis Villeneuve',
            'year' => 2021,
            'runtime_minutes' => 155,
            'genres' => 'Sci-Fi, Adventure',
            'description' => 'Paul Atreides...',
            'poster_url' => 'https://example.test/dune.jpg',
            'imdb_id' => 'tt1160419',
        ];
    }

    public function fetchTVSeasons(int $tmdbId): ?array
    {
        return [
            'title' => 'Game of Thrones',
            'seasons' => [],
        ];
    }
}

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->app->instance(TmdbService::class, new FakeTmdbService);
});

it('puts the query in the URL and derives the results step after searching', function (): void {
    Livewire::actingAs($this->user)
        ->test(MovieTmdbSearch::class)
        ->set('query', 'dune')
        ->call('search')
        ->assertSet('query', 'dune')
        ->assertViewHas('step', 'results')
        ->assertCount('searchResults', 2);
});

it('puts the selection in the URL and derives the configure step', function (): void {
    Livewire::actingAs($this->user)
        ->test(MovieTmdbSearch::class)
        ->set('query', 'dune')
        ->call('search')
        ->call('selectResult', 438631, 'movie')
        ->assertSet('selectedTmdbId', 438631)
        ->assertSet('selectedMediaType', 'movie')
        ->assertSet('title', 'Dune')
        ->assertViewHas('step', 'configure_movie');
});

it('rehydrates a deep-linked result from the URL with no stored state', function (): void {
    // Simulates opening /movies/search-tmdb?q=dune&id=438631&type=movie cold.
    Livewire::actingAs($this->user)
        ->withQueryParams(['q' => 'dune', 'id' => 438631, 'type' => 'movie'])
        ->test(MovieTmdbSearch::class)
        ->assertViewHas('step', 'configure_movie')
        ->assertSet('title', 'Dune')
        ->assertSet('year', 2021);
});

it('recovers the media type on a deep link that omits it', function (): void {
    Livewire::actingAs($this->user)
        ->withQueryParams(['q' => 'dune', 'id' => 1399]) // no type; it's the TV result
        ->test(MovieTmdbSearch::class)
        ->assertSet('selectedMediaType', 'tv')
        ->assertViewHas('step', 'configure_tv');
});

it('going back to results clears the selection so the step derives correctly', function (): void {
    Livewire::actingAs($this->user)
        ->test(MovieTmdbSearch::class)
        ->set('query', 'dune')
        ->call('search')
        ->call('selectResult', 438631, 'movie')
        ->assertViewHas('step', 'configure_movie')
        ->call('backToResults')
        ->assertSet('selectedTmdbId', null)
        ->assertViewHas('step', 'results');
});

it('an empty query derives the search step', function (): void {
    Livewire::actingAs($this->user)
        ->test(MovieTmdbSearch::class)
        ->assertViewHas('step', 'search');
});
