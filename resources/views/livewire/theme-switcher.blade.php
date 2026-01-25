<div x-data="{ open: false }" class="relative">
    <button
        @click="open = !open"
        type="button"
        class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary hover:bg-theme-bg-hover transition-colors"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
        </svg>
        <span class="hidden sm:inline">Theme</span>
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-theme-card-bg shadow-lg ring-1 ring-theme-border-primary"
        style="display: none;"
    >
        <div class="py-1">
            @foreach($themes as $themeOption)
                <button
                    wire:click="setTheme('{{ $themeOption['value'] }}')"
                    @click="open = false; document.documentElement.setAttribute('data-theme', '{{ $themeOption['value'] }}')"
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-left hover:bg-theme-bg-hover transition-colors {{ $theme === $themeOption['value'] ? 'text-theme-accent-primary' : 'text-theme-text-primary' }}"
                >
                    @if($theme === $themeOption['value'])
                        <svg class="h-4 w-4 text-theme-accent-primary" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <span class="w-4"></span>
                    @endif
                    <div>
                        <div class="font-medium">{{ $themeOption['name'] }}</div>
                        <div class="text-xs text-theme-text-muted">{{ $themeOption['description'] }}</div>
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</div>
