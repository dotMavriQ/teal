<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Lets a user manage their own third-party API keys. Keys are stored encrypted
 * and never rendered back in full — only a masked hint (`••••3f2a`).
 */
class ApiKeysForm extends Component
{
    /**
     * Plaintext entered by the user, keyed by credential slug. Always starts
     * empty and is cleared after saving so a full key is never re-rendered.
     *
     * @var array<string, string>
     */
    public array $inputs = [];

    public function mount(): void
    {
        foreach ($this->slugs() as $slug) {
            $this->inputs[$slug] = '';
        }
    }

    public function save(): void
    {
        if ($this->policy() === 'shared') {
            return;
        }

        /** @var array<string, array<string, string|null>> $rules */
        $rules = [];
        foreach ($this->slugs() as $slug) {
            $rules["inputs.$slug"] = ['nullable', 'string', 'max:500'];
        }
        $this->validate($rules);

        $user = $this->user();
        $stored = 0;

        foreach ($this->slugs() as $slug) {
            $value = trim($this->inputs[$slug] ?? '');
            if ($value === '') {
                continue;
            }

            $user->apiKeys()->updateOrCreate(
                ['service' => $slug],
                ['key' => $value],
            );
            $this->inputs[$slug] = '';
            $stored++;
        }

        $user->unsetRelation('apiKeys');

        if ($stored > 0) {
            session()->flash('api-keys-status', "Saved {$stored} key".($stored === 1 ? '' : 's').'.');
        }
    }

    public function remove(string $slug): void
    {
        $this->user()->apiKeys()->where('service', $slug)->delete();
        $this->user()->unsetRelation('apiKeys');
        session()->flash('api-keys-status', 'Key removed.');
    }

    public function render(): View
    {
        return view('livewire.profile.api-keys-form', [
            'services' => config('api_keys.services', []),
            'states' => $this->states(),
            'policy' => $this->policy(),
        ]);
    }

    /**
     * Per-slug display state: where the effective value comes from and a masked
     * hint for a user-set key. Never contains a full key.
     *
     * @return array<string, array{source: string, masked: string|null}>
     */
    protected function states(): array
    {
        $user = $this->user();
        $states = [];

        foreach ($this->fields() as $slug => $configPath) {
            $userKey = $user->apiKeyFor($slug);
            if ($userKey !== null && $userKey !== '') {
                $states[$slug] = ['source' => 'user', 'masked' => $this->mask($userKey)];

                continue;
            }

            $instance = config($configPath);
            $states[$slug] = [
                'source' => is_string($instance) && $instance !== '' ? 'instance' : 'none',
                'masked' => null,
            ];
        }

        return $states;
    }

    protected function mask(string $key): string
    {
        $tail = mb_substr($key, -4);

        return '••••'.($tail === '' ? '' : $tail);
    }

    /**
     * @return array<string, string> slug => config path
     */
    protected function fields(): array
    {
        /** @var array<string, array{fields: array<string, array{config: string}>}> $services */
        $services = config('api_keys.services', []);

        $fields = [];
        foreach ($services as $service) {
            foreach ($service['fields'] as $slug => $field) {
                $fields[$slug] = $field['config'];
            }
        }

        return $fields;
    }

    /**
     * @return list<string>
     */
    protected function slugs(): array
    {
        return array_keys($this->fields());
    }

    protected function policy(): string
    {
        $policy = config('api_keys.policy', 'both');

        return is_string($policy) ? $policy : 'both';
    }

    protected function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
