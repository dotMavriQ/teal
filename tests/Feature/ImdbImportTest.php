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

    public function test_parse_tv_series_entry(): void
    {
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt0903747,8,2025-01-01,"Breaking Bad","Breaking Bad",https://www.imdb.com/title/tt0903747,TV Series,9.5,,2008,"Drama, Crime, Thriller",2300000,2008-01-20,"Vince Gilligan"
CSV;

        $result = $this->service->parseCSV($csv);
        $shows = $result['shows'];

        $this->assertCount(1, $shows);
        $this->assertEquals('Breaking Bad', $shows[0]['title']);
        $this->assertEquals('tt0903747', $shows[0]['imdb_id']);
        $this->assertEquals(8, $shows[0]['rating']);
        $this->assertNull($shows[0]['runtime_minutes'] ?? null);
    }

    public function test_parse_episode_string_with_dot_notation(): void
    {
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt21338724,7,2025-11-09,"Monsieur Spade: Episode #1.6","Monsieur Spade: Episode #1.6",https://www.imdb.com/title/tt21338724,TV Episode,6.6,,2024,"Thriller, Crime, Drama",435,2024-02-18,"Scott Frank"
CSV;

        $result = $this->service->parseCSV($csv);
        $episodes = $result['episodes'];

        $this->assertCount(1, $episodes);
        $this->assertEquals('Monsieur Spade', $episodes[0]['show_name']);
        $this->assertEquals(1, $episodes[0]['season_number']);
        $this->assertEquals(6, $episodes[0]['episode_number']);
        $this->assertEquals(7, $episodes[0]['rating']);
    }

    public function test_parse_episode_string_with_s_e_notation(): void
    {
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt12345678,8,2025-01-01,"Breaking Bad: Episode #S01E01","Breaking Bad: Episode #S01E01",https://www.imdb.com/title/tt12345678,TV Episode,8.5,,2008,"Drama",1000,2008-01-20,"Vince Gilligan"
CSV;

        $result = $this->service->parseCSV($csv);
        $episodes = $result['episodes'];

        $this->assertCount(1, $episodes);
        $this->assertEquals('Breaking Bad', $episodes[0]['show_name']);
        $this->assertEquals(1, $episodes[0]['season_number']);
        $this->assertEquals(1, $episodes[0]['episode_number']);
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

    public function test_import_show(): void
    {
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt0903747,8,2025-01-01,"Breaking Bad","Breaking Bad",https://www.imdb.com/title/tt0903747,TV Series,9.5,,2008,"Drama, Crime, Thriller",2300000,2008-01-20,"Vince Gilligan"
CSV;

        $categorized = $this->service->parseCSV($csv);
        $result = $this->service->importAll($this->user, $categorized);

        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['skipped']);

        $this->assertDatabaseHas('shows', [
            'user_id' => $this->user->id,
            'title' => 'Breaking Bad',
            'imdb_id' => 'tt0903747',
        ]);
    }

    public function test_import_episode_creates_placeholder_show(): void
    {
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt21338724,7,2025-11-09,"Monsieur Spade: Episode #1.6","Monsieur Spade: Episode #1.6",https://www.imdb.com/title/tt21338724,TV Episode,6.6,,2024,"Thriller, Crime, Drama",435,2024-02-18,"Scott Frank"
CSV;

        $categorized = $this->service->parseCSV($csv);
        $result = $this->service->importAll($this->user, $categorized);

        $this->assertEquals(1, $result['imported']);

        // Check that placeholder show was created
        $show = Show::where('user_id', $this->user->id)
            ->where('title', 'Monsieur Spade')
            ->first();

        $this->assertNotNull($show);

        // Check that episode was created
        $episode = Episode::where('show_id', $show->id)
            ->where('user_id', $this->user->id)
            ->where('season_number', 1)
            ->where('episode_number', 6)
            ->first();

        $this->assertNotNull($episode);
        $this->assertEquals(7, $episode->rating);
    }

    public function test_import_skips_duplicates(): void
    {
        $movie = Movie::factory()->create([
            'user_id' => $this->user->id,
            'imdb_id' => 'tt0111161',
        ]);

        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt0111161,9,2025-01-01,"The Shawshank Redemption","The Shawshank Redemption",https://www.imdb.com/title/tt0111161,Movie,9.3,142,1994,"Drama, Crime",2500000,1994-10-14,"Frank Darabont"
CSV;

        $categorized = $this->service->parseCSV($csv);
        $result = $this->service->importAll($this->user, $categorized, skipDuplicates: true);

        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(1, $result['skipped']);
        
        // Verify only one movie exists
        $this->assertEquals(1, Movie::where('user_id', $this->user->id)->where('imdb_id', 'tt0111161')->count());
    }

    public function test_import_multiple_episodes_for_same_show(): void
    {
        $csv = <<<CSV
Const,Your Rating,Date Rated,Title,Original Title,URL,Title Type,IMDb Rating,Runtime (mins),Year,Genres,Num Votes,Release Date,Directors
tt12345678,8,2025-01-01,"Breaking Bad: Episode #S01E01","Breaking Bad: Episode #S01E01",https://www.imdb.com/title/tt12345678,TV Episode,8.5,,2008,"Drama",1000,2008-01-20,"Vince Gilligan"
tt12345679,8,2025-01-02,"Breaking Bad: Episode #S01E02","Breaking Bad: Episode #S01E02",https://www.imdb.com/title/tt12345679,TV Episode,8.4,,2008,"Drama",1000,2008-01-27,"Vince Gilligan"
CSV;

        $categorized = $this->service->parseCSV($csv);
        $result = $this->service->importAll($this->user, $categorized);

        $this->assertEquals(2, $result['imported']);

        // Check that only one show was created
        $show = Show::where('user_id', $this->user->id)
            ->where('title', 'Breaking Bad')
            ->first();

        $this->assertNotNull($show);

        // Check that both episodes exist
        $this->assertEquals(2, Episode::where('show_id', $show->id)->count());
    }
}
