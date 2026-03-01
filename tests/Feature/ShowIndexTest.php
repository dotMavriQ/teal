<?php

declare(strict_types=1);

use App\Enums\WatchingStatus;
use App\Livewire\Shows\ShowIndex;
use App\Models\Show;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('renders the show index page', function () {
    $this->actingAs($this->user)
        ->get(route('shows.index'))
        ->assertOk();
});

it('requires authentication', function () {
    $this->get(route('shows.index'))
        ->assertRedirect(route('login'));
});

it('displays shows for the authenticated user', function () {
    Show::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'My Test Show',
    ]);

    Livewire::actingAs($this->user)
        ->test(ShowIndex::class)
        ->assertSee('My Test Show');
});

it('filters shows by status', function () {
    Show::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Watching Show',
        'status' => WatchingStatus::Watching,
    ]);
    Show::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Watched Show',
        'status' => WatchingStatus::Watched,
    ]);

    Livewire::actingAs($this->user)
        ->test(ShowIndex::class)
        ->set('status', 'watching')
        ->assertSee('Watching Show')
        ->assertDontSee('Watched Show');
});

it('does not show other users shows', function () {
    $otherUser = User::factory()->create();
    Show::factory()->create([
        'user_id' => $otherUser->id,
        'title' => 'Other Users Show',
    ]);

    Livewire::actingAs($this->user)
        ->test(ShowIndex::class)
        ->assertDontSee('Other Users Show');
});
