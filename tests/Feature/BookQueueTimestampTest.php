<?php

declare(strict_types=1);

use App\Enums\ReadingStatus;
use App\Livewire\Books\BookIndex;
use App\Livewire\Books\ReadQueue;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

/**
 * Create `count` queued books (positions 1..count) whose updated_at is
 * backdated to a known point, so we can detect any spurious "touch".
 */
function queuedBooks(User $user, int $count, Carbon $backdatedTo): Collection
{
    $books = collect(range(1, $count))->map(fn (int $position) => Book::factory()->wantToRead()->create([
        'user_id' => $user->id,
        'queue_position' => $position,
    ]));

    Book::withoutTimestamps(fn () => Book::whereIn('id', $books->pluck('id'))
        ->update(['updated_at' => $backdatedTo]));

    return $books->each->refresh();
}

it('does not bump other queued books updated_at when one is marked read', function (): void {
    $past = now()->subDays(10);
    [$first, $second, $third] = queuedBooks($this->user, 3, $past)->all();

    // Marking the top-of-queue book read auto-removes it and re-numbers the rest.
    Livewire::actingAs($this->user)
        ->test(BookIndex::class)
        ->call('updateStatus', $first->id, 'read');

    $second->refresh();
    $third->refresh();

    // The shifted books were re-numbered...
    expect($second->queue_position)->toBe(1)
        ->and($third->queue_position)->toBe(2);

    // ...but their updated_at must be untouched (this is the regression we fixed).
    expect($second->updated_at->format('Y-m-d H:i:s'))->toBe($past->format('Y-m-d H:i:s'))
        ->and($third->updated_at->format('Y-m-d H:i:s'))->toBe($past->format('Y-m-d H:i:s'));

    // The book we actually changed *should* be updated for real.
    $first->refresh();
    expect($first->status)->toBe(ReadingStatus::Read)
        ->and($first->queue_position)->toBeNull()
        ->and($first->updated_at->timestamp)->toBeGreaterThan($past->timestamp);
});

it('does not bump other queued books updated_at when one is removed from the queue', function (): void {
    $past = now()->subDays(10);
    [$first, $second, $third] = queuedBooks($this->user, 3, $past)->all();

    Livewire::actingAs($this->user)
        ->test(ReadQueue::class)
        ->call('removeFromQueue', $first->id);

    $second->refresh();
    $third->refresh();

    expect($second->queue_position)->toBe(1)
        ->and($third->queue_position)->toBe(2)
        ->and($second->updated_at->format('Y-m-d H:i:s'))->toBe($past->format('Y-m-d H:i:s'))
        ->and($third->updated_at->format('Y-m-d H:i:s'))->toBe($past->format('Y-m-d H:i:s'));
});

it('does not bump updated_at when reordering the queue', function (): void {
    $past = now()->subDays(10);
    [$first, $second, $third] = queuedBooks($this->user, 3, $past)->all();

    // moveToTop exercises the mass increment() branch.
    Livewire::actingAs($this->user)
        ->test(ReadQueue::class)
        ->call('moveToTop', $third->id);

    $first->refresh();
    $second->refresh();
    $third->refresh();

    // Positions reshuffled: the third book is now first.
    expect($third->queue_position)->toBe(1)
        ->and($first->queue_position)->toBe(2)
        ->and($second->queue_position)->toBe(3);

    // No book's updated_at was touched by the reorder.
    expect($first->updated_at->format('Y-m-d H:i:s'))->toBe($past->format('Y-m-d H:i:s'))
        ->and($second->updated_at->format('Y-m-d H:i:s'))->toBe($past->format('Y-m-d H:i:s'))
        ->and($third->updated_at->format('Y-m-d H:i:s'))->toBe($past->format('Y-m-d H:i:s'));
});
