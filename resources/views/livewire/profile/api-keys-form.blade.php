<section>
    <header>
        <h2 class="text-lg font-medium text-theme-text-primary">
            {{ __('API Keys') }}
        </h2>

        <p class="mt-1 text-sm text-theme-text-tertiary">
            {{ __('Optionally provide your own keys for the external metadata services. Keys are encrypted at rest and used only for your account. OpenLibrary and Jikan (anime) need no key.') }}
        </p>

        @if ($policy === 'byo')
            <p class="mt-2 text-sm text-theme-warning-text">
                {{ __('This instance requires you to bring your own keys — there is no shared fallback.') }}
            </p>
        @endif
    </header>

    @if ($policy === 'shared')
        <p class="mt-6 text-sm text-theme-text-secondary">
            {{ __('The administrator has configured shared instance keys, so per-user keys are disabled here.') }}
        </p>
    @else
        @if (session('api-keys-status'))
            <p class="mt-4 text-sm font-medium text-theme-success">{{ session('api-keys-status') }}</p>
        @endif

        <form wire:submit="save" class="mt-6 space-y-8">
            @foreach ($services as $service)
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-theme-text-primary">{{ $service['label'] }}</h3>
                        <p class="text-xs text-theme-text-muted">{{ $service['description'] }}</p>
                    </div>

                    @foreach ($service['fields'] as $slug => $field)
                        @php($state = $states[$slug])
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <x-input-label :for="$slug" :value="$field['label']" />

                                @if ($state['source'] === 'user')
                                    <span class="inline-flex items-center gap-2 text-xs text-theme-text-tertiary">
                                        <span class="font-mono">{{ $state['masked'] }}</span>
                                        <button type="button" wire:click="remove('{{ $slug }}')"
                                            class="text-theme-danger-text hover:underline">{{ __('Remove') }}</button>
                                    </span>
                                @elseif ($state['source'] === 'instance')
                                    <span class="text-xs text-theme-text-muted">{{ __('Using instance key') }}</span>
                                @else
                                    <span class="text-xs text-theme-text-muted">{{ __('Not set') }}</span>
                                @endif
                            </div>

                            <x-text-input
                                wire:model="inputs.{{ $slug }}"
                                :id="$slug"
                                type="password"
                                autocomplete="off"
                                class="mt-1 block w-full"
                                :placeholder="$state['source'] === 'user' ? __('Enter a new value to replace') : __('Paste your key')" />
                            <x-input-error class="mt-2" :messages="$errors->get('inputs.'.$slug)" />
                        </div>
                    @endforeach
                </div>
            @endforeach

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save keys') }}</x-primary-button>
            </div>
        </form>
    @endif
</section>
