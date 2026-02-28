<?php

declare(strict_types=1);

use App\Enums\WatchingStatus;
use App\Livewire\Anime\AnimeIndex;
use App\Models\Anime;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('renders the anime index page', function () {
    $this->actingAs($this->user)
        ->get(route('anime.index'))
        ->assertOk();
});

it('requires authentication', function () {
    $this->get(route('anime.index'))
        ->assertRedirect(route('login'));
});

it('displays anime for the authenticated user', function () {
    Anime::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'My Test Anime',
    ]);

    Livewire::actingAs($this->user)
        ->test(AnimeIndex::class)
        ->assertSee('My Test Anime');
});

it('filters anime by status', function () {
    Anime::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Watching Anime',
        'status' => WatchingStatus::Watching,
    ]);
    Anime::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Watched Anime',
        'status' => WatchingStatus::Watched,
    ]);

    Livewire::actingAs($this->user)
        ->test(AnimeIndex::class)
        ->set('status', 'watching')
        ->assertSee('Watching Anime')
        ->assertDontSee('Watched Anime');
});

it('does not show other users anime', function () {
    $otherUser = User::factory()->create();
    Anime::factory()->create([
        'user_id' => $otherUser->id,
        'title' => 'Other Users Anime',
    ]);

    Livewire::actingAs($this->user)
        ->test(AnimeIndex::class)
        ->assertDontSee('Other Users Anime');
});
