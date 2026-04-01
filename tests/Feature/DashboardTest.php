<?php

declare(strict_types=1);

use App\Livewire\Dashboard;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('renders the dashboard', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk();
});

it('requires authentication', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('displays category cards', function () {
    Livewire::actingAs($this->user)
        ->test(Dashboard::class)
        ->assertSee('Watching')
        ->assertSee('Reading')
        ->assertSee('Playing')
        ->assertSee('Listening');
});

it('shows all four category cards as active links', function () {
    Livewire::actingAs($this->user)
        ->test(Dashboard::class)
        ->assertSee('Watching')
        ->assertSee('Reading')
        ->assertSee('Playing')
        ->assertSee('Listening');
});
