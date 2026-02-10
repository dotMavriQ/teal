<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\Show;
use App\Models\User;
use App\Services\ImdbImportService;
use Tests\TestCase;

class ImdbImportTest extends TestCase
{
    private ImdbImportService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ImdbImportService;
        $this->user = User::factory()->create();
    }

    public function test_parse_csv_with_valid_headers(): void
    {
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt0111161,9,2025-01-01,"The Shawshank Redemption","The Shawshank Redemption",https://www.imdb.com/title/tt0111161,Movie,9.3,142,1994,"Drama, Crime",2500000,1994-10-14,"Frank Darabont"
CSV;

        $result = $this->service->parseCSV($csv);

        $this->assertIsObject($result);
        $this->assertTrue($result->has('movies'));
        $this->assertTrue($result->has('shows'));
        $this->assertTrue($result->has('episodes'));
    }

    public function test_parse_movie_entry(): void
    {
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt0111161,9,2025-01-01,"The Shawshank Redemption","The Shawshank Redemption",https://www.imdb.com/title/tt0111161,Movie,9.3,142,1994,"Drama, Crime",2500000,1994-10-14,"Frank Darabont"
CSV;

        $result = $this->service->parseCSV($csv);
        $movies = $result['movies'];

        $this->assertCount(1, $movies);
        $this->assertEquals('The Shawshank Redemption', $movies[0]['title']);
        $this->assertEquals('tt0111161', $movies[0]['imdb_id']);
        $this->assertEquals(9, $movies[0]['rating']);
        $this->assertEquals(142, $movies[0]['runtime_minutes']);
    }

    public function test_import_movie(): void
    {
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt0111161,9,2025-01-01,"The Shawshank Redemption","The Shawshank Redemption",https://www.imdb.com/title/tt0111161,Movie,9.3,142,1994,"Drama, Crime",2500000,1994-10-14,"Frank Darabont"
CSV;

        $categorized = $this->service->parseCSV($csv);
        $result = $this->service->importAll($this->user, $categorized);

        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertCount(0, $result['errors']);

        $this->assertDatabaseHas('movies', [
            'user_id' => $this->user->id,
            'title' => 'The Shawshank Redemption',
            'imdb_id' => 'tt0111161',
            'rating' => 9,
        ]);
    }

    public function test_non_destructive_movie_update(): void
    {
        // Create movie with some data
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'imdb_id' => 'tt0111161',
            'title' => 'The Shawshank Redemption',
            'rating' => 10,
            'review' => 'This is my custom review',
            'notes' => 'My personal notes',
            'description' => null,
            'year' => null,
        ]);

        // Now import the same movie from CSV with different data
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt0111161,9,2025-01-01,"The Shawshank Redemption","The Shawshank Redemption",https://www.imdb.com/title/tt0111161,Movie,9.3,142,1994,"Drama, Crime",2500000,1994-10-14,"Frank Darabont"
CSV;

        $categorized = $this->service->parseCSV($csv);
        $result = $this->service->importAll($this->user, $categorized);

        // Should update only empty fields
        $this->assertEquals(1, $result['imported']);

        $movie->refresh();

        // User's custom data should be preserved
        $this->assertEquals(10, $movie->rating);
        $this->assertEquals('This is my custom review', $movie->review);
        $this->assertEquals('My personal notes', $movie->notes);

        // Empty fields should be filled
        $this->assertEquals(1994, $movie->year);
    }

    public function test_non_destructive_show_update(): void
    {
        // Create show with some data
        $show = Show::factory()->create([
            'user_id' => $this->user->id,
            'imdb_id' => 'tt0903747',
            'title' => 'Breaking Bad',
            'rating' => 10,
            'description' => null,
            'imdb_rating' => null,
        ]);

        // Import same show from CSV
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt0903747,8,2025-01-01,"Breaking Bad","Breaking Bad",https://www.imdb.com/title/tt0903747,TV Series,9.5,,2008,"Drama, Crime, Thriller",2300000,2008-01-20,"Vince Gilligan"
CSV;

        $categorized = $this->service->parseCSV($csv);
        $result = $this->service->importAll($this->user, $categorized);

        $this->assertEquals(1, $result['imported']);

        $show->refresh();

        // User's rating should be preserved
        $this->assertEquals(10, $show->rating);

        // Empty fields should be filled
        $this->assertEquals(9.5, $show->imdb_rating);
    }

    public function test_non_destructive_episode_update(): void
    {
        // Create show first
        $show = Show::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Breaking Bad',
        ]);

        // Create episode with custom data
        $episode = Episode::factory()->create([
            'show_id' => $show->id,
            'user_id' => $this->user->id,
            'imdb_id' => 'tt12345678',
            'season_number' => 1,
            'episode_number' => 1,
            'rating' => 10,
            'runtime_minutes' => null,
        ]);

        // Import same episode from CSV
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt12345678,8,2025-01-01,"Breaking Bad: Episode #S01E01","Breaking Bad: Episode #S01E01",https://www.imdb.com/title/tt12345678,TV Episode,8.5,45,2008,"Drama",1000,2008-01-20,"Vince Gilligan"
CSV;

        $categorized = $this->service->parseCSV($csv);
        $result = $this->service->importAll($this->user, $categorized);

        $this->assertEquals(1, $result['imported']);

        $episode->refresh();

        // User's rating should be preserved
        $this->assertEquals(10, $episode->rating);

        // Empty fields should be filled
        $this->assertEquals(45, $episode->runtime_minutes);
    }
}
