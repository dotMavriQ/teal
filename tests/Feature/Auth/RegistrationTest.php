<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Livewire\Volt\Volt;

test('registration screen is disabled', function (): void {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('new users can register', function (): void {
    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
