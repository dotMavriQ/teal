<?php

declare(strict_types=1);

use App\Enums\ReadingStatus;
use App\Livewire\Books\BookIndex;
use App\Models\Book;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('renders the book index page', function () {
    $this->actingAs($this->user)
        ->get(route('books.index'))
        ->assertOk();
});

it('requires authentication', function () {
    $this->get(route('books.index'))
        ->assertRedirect(route('login'));
});

it('displays books for the authenticated user', function () {
    $book = Book::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'My Test Book',
    ]);

    Livewire::actingAs($this->user)
        ->test(BookIndex::class)
        ->assertSee('My Test Book');
});

it('filters books by status', function () {
    Book::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Currently Reading Book',
        'status' => ReadingStatus::Reading,
    ]);
    Book::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Finished Book',
        'status' => ReadingStatus::Read,
    ]);

    Livewire::actingAs($this->user)
        ->test(BookIndex::class)
        ->set('status', 'reading')
        ->assertSee('Currently Reading Book')
        ->assertDontSee('Finished Book');
});

it('does not show other users books', function () {
    $otherUser = User::factory()->create();
    Book::factory()->create([
        'user_id' => $otherUser->id,
        'title' => 'Other Users Book',
    ]);

    Livewire::actingAs($this->user)
        ->test(BookIndex::class)
        ->assertDontSee('Other Users Book');
});
