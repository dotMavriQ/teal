<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Livewire\Volt\Volt;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.register');
});

test('new users can register', function () {
    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('usernames must be unique', function () {
    $existing = User::factory()->create([
        'name' => 'duplicate',
        'email' => 'first@example.com',
    ]);

    $component = Volt::test('pages.auth.register')
        ->set('name', $existing->name)
        ->set('email', 'second@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertHasErrors(['name' => 'unique']);
    $this->assertGuest();
});
