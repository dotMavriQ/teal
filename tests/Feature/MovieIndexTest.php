<?php

declare(strict_types=1);

use App\Enums\WatchingStatus;
use App\Livewire\Movies\MovieIndex;
use App\Models\Movie;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('renders the movie index page', function () {
    $this->actingAs($this->user)
        ->get(route('movies.index'))
        ->assertOk();
});

it('requires authentication', function () {
    $this->get(route('movies.index'))
        ->assertRedirect(route('login'));
});

it('displays movies for the authenticated user', function () {
    Movie::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'My Test Movie',
    ]);

    Livewire::actingAs($this->user)
        ->test(MovieIndex::class)
        ->assertSee('My Test Movie');
});

it('filters movies by status', function () {
    Movie::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Watching Movie',
        'status' => WatchingStatus::Watching,
    ]);
    Movie::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Watched Movie',
        'status' => WatchingStatus::Watched,
    ]);

    Livewire::actingAs($this->user)
        ->test(MovieIndex::class)
        ->set('status', 'watching')
        ->assertSee('Watching Movie')
        ->assertDontSee('Watched Movie');
});

it('does not show other users movies', function () {
    $otherUser = User::factory()->create();
    Movie::factory()->create([
        'user_id' => $otherUser->id,
        'title' => 'Other Users Movie',
    ]);

    Livewire::actingAs($this->user)
        ->test(MovieIndex::class)
        ->assertDontSee('Other Users Movie');
});
