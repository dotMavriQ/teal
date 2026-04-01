<div>
    {{-- Header --}}
    <header class="bg-theme-bg-primary shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-2 text-sm">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-theme-text-muted hover:text-theme-text-secondary">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li>
                        <a href="{{ route('playing.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Playing</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li>
                        <a href="{{ route('board-games.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Board Games</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li><span class="text-theme-text-tertiary">Discover</span></li>
                </ol>
            </nav>
            <h1 class="mt-1 text-2xl font-bold text-theme-text-primary">Discover Board Games</h1>
        </div>
    </header>

    <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="mb-4 rounded-md bg-theme-success-bg p-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-theme-success" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm font-medium text-theme-success-text">{{ session('message') }}</p>
                </div>
            @endif

            {{-- ===== STEP: SEARCH ===== --}}
            @if($step === 'search')
                <div class="max-w-2xl mx-auto">
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg p-6">
                        <div class="text-center mb-6">
                            {{-- Dice / board game icon --}}
                            <svg class="mx-auto h-12 w-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <h2 class="mt-2 text-lg font-semibold text-theme-text-primary">Discover Board Games</h2>
                            <p class="mt-1 text-sm text-theme-text-secondary">Search BoardGameGeek to find games and add them to your library.</p>
                        </div>
                        <form wire:submit="search" class="space-y-3">
                            <div class="flex gap-3">
                                <input
                                    wire:model="query"
                                    type="text"
                                    placeholder="Search by game title..."
                                    class="flex-1 rounded-md border-0 py-2 px-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                                    autofocus
                                >
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            {{-- ===== STEP: RESULTS ===== --}}
            @elseif($step === 'results')
                <div class="mb-4 flex items-center justify-between">
                    <button wire:click="backToSearch" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        New Search
                    </button>
                    <form wire:submit="search" class="flex gap-2">
                        <input
                            wire:model="query"
                            type="text"
                            class="rounded-md border-0 py-1.5 px-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                        >
                        <button type="submit" class="rounded-md btn-secondary px-3 py-1.5 text-sm font-medium ring-1 ring-inset shadow-sm">Search</button>
                    </form>
                </div>

                @if(empty($searchResults))
                    <div class="text-center py-12">
                        <p class="text-theme-text-secondary">No results found for "{{ $query }}".</p>
                    </div>
                @else
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden">
                        <ul class="divide-y divide-theme-border-primary">
                            @foreach($searchResults as $result)
                                <li wire:key="result-{{ $result['bgg_id'] }}">
                                    <button
                                        wire:click="selectGame({{ $result['bgg_id'] }})"
                                        wire:loading.attr="disabled"
                                        class="w-full flex items-center gap-4 px-4 py-3 text-left hover:bg-theme-bg-hover transition-colors group"
                                    >
                                        {{-- Thumbnail --}}
                                        <div class="w-12 h-16 flex-shrink-0 rounded overflow-hidden bg-theme-bg-tertiary flex items-center justify-center">
                                            @if(!empty($result['thumbnail']))
                                                <img src="{{ $result['thumbnail'] }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                            @else
                                                <svg class="h-6 w-6 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            @endif
                                        </div>

                                        {{-- Info --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-theme-text-primary group-hover:text-theme-accent-primary truncate">
                                                {{ $result['title'] }}
                                            </p>
                                            <p class="text-xs text-theme-text-muted mt-0.5">
                                                @if(!empty($result['year_published']))
                                                    {{ $result['year_published'] }}
                                                @else
                                                    Year unknown
                                                @endif
                                                @if(!empty($result['designer']))
                                                    &middot; {{ $result['designer'] }}
                                                @endif
                                            </p>
                                        </div>

                                        {{-- Arrow --}}
                                        <svg class="h-4 w-4 text-theme-text-muted flex-shrink-0 group-hover:text-theme-accent-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Loading overlay --}}
                <div wire:loading wire:target="selectGame" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
                    <div class="bg-theme-card-bg rounded-lg p-6 shadow-xl ring-1 ring-theme-border-primary flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-theme-accent-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-theme-text-primary">Loading game details...</span>
                    </div>
                </div>

            {{-- ===== STEP: CONFIGURE ===== --}}
            @elseif($step === 'configure')
                <div class="mb-4">
                    <button wire:click="backToResults" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back to Results
                    </button>
                </div>

                <div class="max-w-3xl mx-auto">
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden">
                        <div class="p-6">
                            {{-- Top: cover thumbnail + key info --}}
                            <div class="flex gap-4 mb-6">
                                <div class="w-32 flex-shrink-0">
                                    @if($cover_url)
                                        <img src="{{ $cover_url }}" alt="" class="w-full rounded-md object-cover aspect-[2/3]">
                                    @else
                                        <div class="w-full aspect-[2/3] rounded-md bg-theme-bg-tertiary flex items-center justify-center">
                                            <svg class="h-8 w-8 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="title" class="block text-sm font-medium text-theme-text-primary">Title</label>
                                    <input wire:model="title" type="text" id="title" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">

                                    <div class="mt-3 grid grid-cols-2 gap-3">
                                        <div>
                                            <label for="designer" class="block text-xs font-medium text-theme-text-muted">Designer</label>
                                            <input wire:model="designer" type="text" id="designer" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                        <div>
                                            <label for="publisher" class="block text-xs font-medium text-theme-text-muted">Publisher</label>
                                            <input wire:model="publisher" type="text" id="publisher" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                    </div>

                                    <div class="mt-3 grid grid-cols-3 gap-3">
                                        <div>
                                            <label for="year_published" class="block text-xs font-medium text-theme-text-muted">Year</label>
                                            <input wire:model="year_published" type="number" id="year_published" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-theme-text-muted">Players</label>
                                            <div class="mt-1 flex items-center gap-1">
                                                <input wire:model="min_players" type="number" min="1" placeholder="Min" class="block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                                <span class="text-theme-text-muted text-xs">–</span>
                                                <input wire:model="max_players" type="number" min="1" placeholder="Max" class="block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                            </div>
                                        </div>
                                        <div>
                                            <label for="playing_time" class="block text-xs font-medium text-theme-text-muted">Time (min)</label>
                                            <input wire:model="playing_time" type="number" id="playing_time" min="0" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                    </div>

                                    {{-- Genres (read-only display from BGG) --}}
                                    @if(!empty($genre))
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-theme-text-muted">Genres</label>
                                            <div class="mt-1 flex flex-wrap gap-1.5">
                                                @foreach($genre as $g)
                                                    <span class="inline-flex items-center rounded-full bg-theme-bg-tertiary px-2.5 py-1 text-xs font-medium text-theme-text-secondary ring-1 ring-inset ring-theme-border-primary">
                                                        {{ $g }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-theme-text-primary">Description</label>
                                <textarea wire:model="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"></textarea>
                            </div>

                            {{-- Status + Rating --}}
                            <div class="mb-4 flex flex-wrap items-end gap-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-theme-text-primary">Status</label>
                                    <select wire:model="status" id="status" class="mt-1 block rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-theme-text-primary">Rating</label>
                                    <div class="mt-1 flex items-center gap-1 flex-wrap">
                                        @for($i = 1; $i <= 10; $i++)
                                            <button
                                                wire:click="$set('rating', {{ $rating === $i ? 'null' : $i }})"
                                                type="button"
                                                class="h-8 w-8 rounded-md text-sm font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-theme-accent-primary {{ $i <= ($rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                            >{{ $i }}</button>
                                        @endfor
                                        <span class="ml-2 text-sm text-theme-text-muted">{{ $rating ? $rating . '/10' : 'Not rated' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="mb-6">
                                <label for="notes" class="block text-sm font-medium text-theme-text-primary">Personal Notes</label>
                                <textarea wire:model="notes" id="notes" rows="3" placeholder="Your thoughts..." class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"></textarea>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-theme-border-primary">
                                <button wire:click="backToResults" type="button" class="text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Cancel</button>
                                <button wire:click="save" type="button" class="rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm">
                                    Add to Library
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
