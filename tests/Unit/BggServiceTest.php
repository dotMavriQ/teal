<?php

declare(strict_types=1);

use App\Services\BggService;
use App\Services\Saloon\Bgg\Requests\GetBoardGameDetails;
use App\Services\Saloon\Bgg\Requests\SearchBoardGames;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

/*
|--------------------------------------------------------------------------
| BggService — XML → array normalization via mocked Saloon responses
|--------------------------------------------------------------------------
| BGG returns XML; the transformation lives inside the send path, so we
| fake the connector responses. Boots Laravel (Saloon facade) but no DB.
*/

uses(Tests\TestCase::class);

it('normalizes and de-duplicates search results', function () {
    $xml = <<<'XML'
    <items>
        <item type="boardgame" id="13"><name type="primary" value="Catan"/><yearpublished value="1995"/></item>
        <item type="boardgame" id="9209"><name type="primary" value="Ticket to Ride"/><yearpublished value="2004"/></item>
    </items>
    XML;

    // search() sends the query twice (exact + fuzzy); the same fixture is
    // returned for both, so the shared id (13, 9209) must be de-duplicated.
    Saloon::fake([SearchBoardGames::class => MockResponse::make($xml)]);

    $results = (new BggService)->search('catan');

    expect($results)->toHaveCount(2)
        ->and($results[0])->toBe(['bgg_id' => 13, 'title' => 'Catan', 'year_published' => 1995])
        ->and($results[1])->toMatchArray(['bgg_id' => 9209, 'title' => 'Ticket to Ride']);
});

it('normalizes full board game details', function () {
    $xml = <<<'XML'
    <items>
        <item type="boardgame" id="13">
            <name type="alternate" value="Die Siedler"/>
            <name type="primary" value="Catan"/>
            <yearpublished value="1995"/>
            <image>https://cf/catan.jpg</image>
            <description>Great &amp; fun &lt;b&gt;game&lt;/b&gt;</description>
            <minplayers value="3"/>
            <maxplayers value="4"/>
            <playingtime value="120"/>
            <link type="boardgamecategory" value="Negotiation"/>
            <link type="boardgamedesigner" value="Klaus Teuber"/>
            <link type="boardgamedesigner" value="Second"/>
            <link type="boardgamedesigner" value="Third"/>
            <link type="boardgamedesigner" value="Fourth"/>
            <link type="boardgamepublisher" value="Kosmos"/>
            <link type="boardgamepublisher" value="Mayfair"/>
            <statistics><ratings><average value="7.147"/></ratings></statistics>
        </item>
    </items>
    XML;

    Saloon::fake([GetBoardGameDetails::class => MockResponse::make($xml)]);

    $details = (new BggService)->getDetails(13);

    expect($details)->toMatchArray([
        'bgg_id' => 13,
        'title' => 'Catan',                                  // picks the primary name, not the alternate
        'description' => 'Great & fun game',                 // entities decoded + tags stripped
        'cover_url' => 'https://cf/catan.jpg',
        'year_published' => 1995,
        'designer' => 'Klaus Teuber, Second, Third',         // capped at first 3
        'publisher' => 'Kosmos',                             // first publisher only
        'min_players' => 3,
        'max_players' => 4,
        'playing_time' => 120,
        'genres' => ['Negotiation'],
        'bgg_rating' => 7.15,                                // rounded to 2 dp
    ]);
});

it('returns null when the details response has no item', function () {
    Saloon::fake([GetBoardGameDetails::class => MockResponse::make('<items></items>')]);

    expect((new BggService)->getDetails(999))->toBeNull();
});

it('returns null when the details request fails', function () {
    Saloon::fake([GetBoardGameDetails::class => MockResponse::make('', 500)]);

    expect((new BggService)->getDetails(13))->toBeNull();
});
