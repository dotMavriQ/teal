<section x-data x-init="document.documentElement.setAttribute('data-theme', '{{ $theme }}'); localStorage.setItem('teal-theme', '{{ $theme }}')">
    <header>
        <h2 class="text-lg font-medium text-theme-text-primary">
            {{ __('Theme') }}
        </h2>
        <p class="mt-1 text-sm text-theme-text-tertiary">
            {{ __('Choose your preferred color theme.') }}
        </p>
    </header>

    <div class="mt-6 space-y-3">
        @foreach($themes as $themeOption)
            <label
                class="flex items-center gap-4 p-4 rounded-lg border cursor-pointer transition-colors
                    {{ $theme === $themeOption['value']
                        ? 'border-theme-accent-primary bg-theme-bg-tertiary'
                        : 'border-theme-border-primary hover:bg-theme-bg-hover' }}"
                @click="document.documentElement.setAttribute('data-theme', '{{ $themeOption['value'] }}'); localStorage.setItem('teal-theme', '{{ $themeOption['value'] }}')"
            >
                <input
                    type="radio"
                    wire:click="setTheme('{{ $themeOption['value'] }}')"
                    name="theme"
                    value="{{ $themeOption['value'] }}"
                    {{ $theme === $themeOption['value'] ? 'checked' : '' }}
                    class="h-4 w-4 text-theme-accent-primary border-theme-border-secondary focus:ring-theme-accent-primary"
                >
                <div class="flex-1">
                    <div class="font-medium text-theme-text-primary">{{ $themeOption['name'] }}</div>
                    <div class="text-sm text-theme-text-tertiary">{{ $themeOption['description'] }}</div>
                </div>
                @if($theme === $themeOption['value'])
                    <svg class="h-5 w-5 text-theme-accent-primary" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                @endif
            </label>
        @endforeach
    </div>
</section>
