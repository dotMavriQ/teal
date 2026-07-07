<?php

declare(strict_types=1);

use App\Livewire\Profile\ApiKeysForm;
use App\Models\User;
use App\Models\UserApiKey;
use App\Support\ApiKey;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    config()->set('api_keys.policy', 'both');
    config()->set('services.tmdb.access_token', 'instance-token');
});

it('stores the key encrypted at rest, not in plaintext', function (): void {
    $this->user->apiKeys()->create([
        'service' => 'tmdb_access_token',
        'key' => 'super-secret-user-key',
    ]);

    $raw = DB::table('user_api_keys')->where('user_id', $this->user->id)->value('key');

    expect($raw)->not->toContain('super-secret-user-key');
    expect($this->user->fresh()->apiKeyFor('tmdb_access_token'))->toBe('super-secret-user-key');
});

it('returns null for a decrypt failure instead of throwing', function (): void {
    // Write a value that is not valid ciphertext.
    $key = UserApiKey::factory()->create([
        'user_id' => $this->user->id,
        'service' => 'tmdb_access_token',
    ]);
    DB::table('user_api_keys')->where('id', $key->id)->update(['key' => 'not-encrypted']);

    expect($this->user->fresh()->apiKeyFor('tmdb_access_token'))->toBeNull();
});

describe('ApiKey::resolve', function (): void {
    it('prefers the user key over the instance key (both policy)', function (): void {
        $this->user->apiKeys()->create(['service' => 'tmdb_access_token', 'key' => 'user-token']);

        $this->actingAs($this->user);

        expect(ApiKey::resolve('tmdb_access_token'))->toBe('user-token');
    });

    it('falls back to the instance key when the user has none', function (): void {
        $this->actingAs($this->user);

        expect(ApiKey::resolve('tmdb_access_token'))->toBe('instance-token');
    });

    it('falls back to the instance key when unauthenticated (e.g. queue jobs)', function (): void {
        expect(ApiKey::resolve('tmdb_access_token'))->toBe('instance-token');
    });

    it('ignores user keys and uses only the instance key under the shared policy', function (): void {
        config()->set('api_keys.policy', 'shared');
        $this->user->apiKeys()->create(['service' => 'tmdb_access_token', 'key' => 'user-token']);

        $this->actingAs($this->user);

        expect(ApiKey::resolve('tmdb_access_token'))->toBe('instance-token');
    });

    it('does not fall back to the instance key under the byo policy', function (): void {
        config()->set('api_keys.policy', 'byo');
        $this->actingAs($this->user);

        expect(ApiKey::resolve('tmdb_access_token'))->toBeNull();
    });

    it('never resolves another user\'s key', function (): void {
        $other = User::factory()->create();
        $other->apiKeys()->create(['service' => 'tmdb_access_token', 'key' => 'other-token']);

        $this->actingAs($this->user);

        expect(ApiKey::resolve('tmdb_access_token'))->toBe('instance-token');
    });

    it('returns null for an unknown slug', function (): void {
        expect(ApiKey::resolve('nonexistent.slug'))->toBeNull();
    });
});

describe('ApiKeysForm', function (): void {
    it('saves a key for the authenticated user', function (): void {
        Livewire::actingAs($this->user)
            ->test(ApiKeysForm::class)
            ->set('inputs.tmdb_access_token', 'my-new-key')
            ->call('save')
            ->assertHasNoErrors();

        expect($this->user->fresh()->apiKeyFor('tmdb_access_token'))->toBe('my-new-key');
    });

    it('clears the input after saving so the full key is never re-rendered', function (): void {
        Livewire::actingAs($this->user)
            ->test(ApiKeysForm::class)
            ->set('inputs.tmdb_access_token', 'my-new-key')
            ->call('save')
            ->assertSet('inputs.tmdb_access_token', '');
    });

    it('never renders a saved key in full, only a masked hint', function (): void {
        $this->user->apiKeys()->create(['service' => 'tmdb_access_token', 'key' => 'abcdefabcdef1234']);

        Livewire::actingAs($this->user)
            ->test(ApiKeysForm::class)
            ->assertDontSee('abcdefabcdef1234')
            ->assertSee('••••1234');
    });

    it('removes a key', function (): void {
        $this->user->apiKeys()->create(['service' => 'tmdb_access_token', 'key' => 'to-be-removed']);

        Livewire::actingAs($this->user)
            ->test(ApiKeysForm::class)
            ->call('remove', 'tmdb_access_token');

        expect($this->user->fresh()->apiKeyFor('tmdb_access_token'))->toBeNull();
    });

    it('does not persist keys under the shared policy', function (): void {
        config()->set('api_keys.policy', 'shared');

        Livewire::actingAs($this->user)
            ->test(ApiKeysForm::class)
            ->set('inputs.tmdb_access_token', 'blocked')
            ->call('save');

        expect($this->user->fresh()->apiKeyFor('tmdb_access_token'))->toBeNull();
    });
});

it('cascade deletes keys when the user is deleted', function (): void {
    $this->user->apiKeys()->create(['service' => 'tmdb_access_token', 'key' => 'k']);
    $this->user->delete();

    expect(UserApiKey::count())->toBe(0);
});
