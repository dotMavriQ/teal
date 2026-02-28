<?php

declare(strict_types=1);

use App\Enums\WatchingStatus;
use App\Models\Anime;
use App\Models\User;
use App\Services\MalImportService;

beforeEach(function () {
    $this->service = new MalImportService;
    $this->user = User::factory()->create();
});

it('parses a valid MAL XML export', function () {
    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<myanimelist>
    <anime>
        <series_animedb_id>1</series_animedb_id>
        <series_title>Cowboy Bebop</series_title>
        <series_episodes>26</series_episodes>
        <my_watched_episodes>26</my_watched_episodes>
        <my_score>10</my_score>
        <my_status>2</my_status>
        <my_start_date>2020-01-15</my_start_date>
        <my_finish_date>2020-02-10</my_finish_date>
        <my_tags></my_tags>
        <my_comments></my_comments>
    </anime>
    <anime>
        <series_animedb_id>5114</series_animedb_id>
        <series_title>Fullmetal Alchemist: Brotherhood</series_title>
        <series_episodes>64</series_episodes>
        <my_watched_episodes>30</my_watched_episodes>
        <my_score>9</my_score>
        <my_status>1</my_status>
        <my_start_date>2024-06-01</my_start_date>
        <my_finish_date>0000-00-00</my_finish_date>
        <my_tags></my_tags>
        <my_comments></my_comments>
    </anime>
</myanimelist>
XML;

    $entries = $this->service->parseXml($xml);

    expect($entries)->toHaveCount(2);
    expect($entries[0]['title'])->toBe('Cowboy Bebop');
    expect($entries[0]['mal_id'])->toBe(1);
    expect($entries[0]['status'])->toBe(WatchingStatus::Watched);
    expect($entries[0]['rating'])->toBe(10);
    expect($entries[0]['episodes_total'])->toBe(26);
    expect($entries[0]['episodes_watched'])->toBe(26);
    expect($entries[0]['date_started'])->toBe('2020-01-15');
    expect($entries[0]['date_finished'])->toBe('2020-02-10');

    expect($entries[1]['title'])->toBe('Fullmetal Alchemist: Brotherhood');
    expect($entries[1]['status'])->toBe(WatchingStatus::Watching);
    expect($entries[1]['date_finished'])->toBeNull();
});

it('maps MAL statuses correctly', function () {
    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<myanimelist>
    <anime>
        <series_animedb_id>1</series_animedb_id>
        <series_title>Watching</series_title>
        <series_episodes>12</series_episodes>
        <my_watched_episodes>0</my_watched_episodes>
        <my_score>0</my_score>
        <my_status>1</my_status>
        <my_start_date>0000-00-00</my_start_date>
        <my_finish_date>0000-00-00</my_finish_date>
        <my_tags></my_tags>
        <my_comments></my_comments>
    </anime>
    <anime>
        <series_animedb_id>2</series_animedb_id>
        <series_title>Completed</series_title>
        <series_episodes>12</series_episodes>
        <my_watched_episodes>12</my_watched_episodes>
        <my_score>8</my_score>
        <my_status>2</my_status>
        <my_start_date>0000-00-00</my_start_date>
        <my_finish_date>0000-00-00</my_finish_date>
        <my_tags></my_tags>
        <my_comments></my_comments>
    </anime>
    <anime>
        <series_animedb_id>3</series_animedb_id>
        <series_title>On Hold</series_title>
        <series_episodes>12</series_episodes>
        <my_watched_episodes>0</my_watched_episodes>
        <my_score>0</my_score>
        <my_status>3</my_status>
        <my_start_date>0000-00-00</my_start_date>
        <my_finish_date>0000-00-00</my_finish_date>
        <my_tags></my_tags>
        <my_comments></my_comments>
    </anime>
</myanimelist>
XML;

    $entries = $this->service->parseXml($xml);

    expect($entries[0]['status'])->toBe(WatchingStatus::Watching);
    expect($entries[1]['status'])->toBe(WatchingStatus::Watched);
    expect($entries[2]['status'])->toBe(WatchingStatus::Watchlist);
});

it('imports anime into the database', function () {
    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<myanimelist>
    <anime>
        <series_animedb_id>1</series_animedb_id>
        <series_title>Test Anime</series_title>
        <series_episodes>12</series_episodes>
        <my_watched_episodes>12</my_watched_episodes>
        <my_score>8</my_score>
        <my_status>2</my_status>
        <my_start_date>0000-00-00</my_start_date>
        <my_finish_date>0000-00-00</my_finish_date>
        <my_tags></my_tags>
        <my_comments></my_comments>
    </anime>
</myanimelist>
XML;

    $entries = $this->service->parseXml($xml);
    $result = $this->service->importAll($this->user, $entries);

    expect($result['imported'])->toBe(1);
    expect($result['skipped'])->toBe(0);
    $this->assertDatabaseHas('anime', [
        'user_id' => $this->user->id,
        'title' => 'Test Anime',
        'mal_id' => 1,
    ]);
});

it('detects duplicates by mal_id', function () {
    Anime::factory()->create([
        'user_id' => $this->user->id,
        'mal_id' => 1,
    ]);

    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<myanimelist>
    <anime>
        <series_animedb_id>1</series_animedb_id>
        <series_title>Duplicate Anime</series_title>
        <series_episodes>12</series_episodes>
        <my_watched_episodes>12</my_watched_episodes>
        <my_score>8</my_score>
        <my_status>2</my_status>
        <my_start_date>0000-00-00</my_start_date>
        <my_finish_date>0000-00-00</my_finish_date>
        <my_tags></my_tags>
        <my_comments></my_comments>
    </anime>
</myanimelist>
XML;

    $entries = $this->service->parseXml($xml);
    $result = $this->service->importAll($this->user, $entries);

    expect($result['imported'])->toBe(0);
    expect($result['skipped'])->toBe(1);
});

it('throws on invalid XML', function () {
    $this->service->parseXml('not xml at all');
})->throws(\Exception::class);

it('handles zero score as null rating', function () {
    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<myanimelist>
    <anime>
        <series_animedb_id>1</series_animedb_id>
        <series_title>Unrated</series_title>
        <series_episodes>12</series_episodes>
        <my_watched_episodes>0</my_watched_episodes>
        <my_score>0</my_score>
        <my_status>6</my_status>
        <my_start_date>0000-00-00</my_start_date>
        <my_finish_date>0000-00-00</my_finish_date>
        <my_tags></my_tags>
        <my_comments></my_comments>
    </anime>
</myanimelist>
XML;

    $entries = $this->service->parseXml($xml);

    expect($entries[0]['rating'])->toBeNull();
});
