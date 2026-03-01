<?php

declare(strict_types=1);

use App\Livewire\Books\BookIndex;
use App\Livewire\Movies\MovieIndex;
use App\Livewire\Anime\AnimeIndex;
use App\Livewire\Comics\ComicIndex;
use App\Models\Book;
use App\Models\Movie;
use App\Models\Anime;
use App\Models\Comic;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('sanitizes malicious sortDirection in BookIndex', function () {
    Book::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Safe Book',
    ]);

    Livewire::actingAs($this->user)
        ->test(BookIndex::class)
        ->set('sortDirection', 'desc; DROP TABLE books--')
        ->assertSee('Safe Book')
        ->assertHasNoErrors();
});

it('sanitizes malicious sortBy in BookIndex', function () {
    Book::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Safe Book',
    ]);

    Livewire::actingAs($this->user)
        ->test(BookIndex::class)
        ->set('sortBy', 'title; DROP TABLE books--')
        ->assertSee('Safe Book')
        ->assertHasNoErrors();
});

it('sanitizes malicious sortDirection in MovieIndex', function () {
    Movie::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Safe Movie',
    ]);

    Livewire::actingAs($this->user)
        ->test(MovieIndex::class)
        ->set('sortDirection', 'desc; DROP TABLE movies--')
        ->assertSee('Safe Movie')
        ->assertHasNoErrors();
});

it('sanitizes malicious sortBy in MovieIndex', function () {
    Movie::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Safe Movie',
    ]);

    Livewire::actingAs($this->user)
        ->test(MovieIndex::class)
        ->set('sortBy', 'title; DROP TABLE movies--')
        ->assertSee('Safe Movie')
        ->assertHasNoErrors();
});

it('sanitizes malicious sortDirection in AnimeIndex', function () {
    Anime::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Safe Anime',
    ]);

    Livewire::actingAs($this->user)
        ->test(AnimeIndex::class)
        ->set('sortDirection', 'desc; DROP TABLE anime--')
        ->assertSee('Safe Anime')
        ->assertHasNoErrors();
});

it('sanitizes malicious sortBy in AnimeIndex', function () {
    Anime::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Safe Anime',
    ]);

    Livewire::actingAs($this->user)
        ->test(AnimeIndex::class)
        ->set('sortBy', 'title; DROP TABLE anime--')
        ->assertSee('Safe Anime')
        ->assertHasNoErrors();
});

it('sanitizes malicious sortDirection in ComicIndex', function () {
    Comic::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Safe Comic',
    ]);

    Livewire::actingAs($this->user)
        ->test(ComicIndex::class)
        ->set('sortDirection', 'desc; DROP TABLE comics--')
        ->assertSee('Safe Comic')
        ->assertHasNoErrors();
});

it('sanitizes malicious sortBy in ComicIndex', function () {
    Comic::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Safe Comic',
    ]);

    Livewire::actingAs($this->user)
        ->test(ComicIndex::class)
        ->set('sortBy', 'issue_count; DROP TABLE comics--')
        ->assertSee('Safe Comic')
        ->assertHasNoErrors();
});

it('allows valid sort columns in BookIndex', function () {
    Book::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Test Book',
    ]);

    Livewire::actingAs($this->user)
        ->test(BookIndex::class)
        ->set('sortBy', 'title')
        ->set('sortDirection', 'asc')
        ->assertSee('Test Book')
        ->assertHasNoErrors();
});

it('allows valid sort columns in ComicIndex', function () {
    Comic::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Test Comic',
    ]);

    Livewire::actingAs($this->user)
        ->test(ComicIndex::class)
        ->set('sortBy', 'issue_count')
        ->set('sortDirection', 'desc')
        ->assertSee('Test Comic')
        ->assertHasNoErrors();
});
