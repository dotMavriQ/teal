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
                        <a href="{{ route('watching.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Watching</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li>
                        <a href="{{ route('movies.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Movies</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li><span class="text-theme-text-tertiary">Search TMDB</span></li>
                </ol>
            </nav>
            <h1 class="mt-1 text-2xl font-bold text-theme-text-primary">Search TMDB</h1>
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
            @if (session()->has('error'))
                <div class="mb-4 rounded-md bg-red-900/20 p-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm font-medium text-red-400">{{ session('error') }}</p>
                </div>
            @endif

            {{-- ===== STEP: SEARCH ===== --}}
            @if($step === 'search')
                <div class="max-w-2xl mx-auto">
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg p-6">
                        <div class="text-center mb-6">
                            <svg class="mx-auto h-12 w-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <h2 class="mt-2 text-lg font-semibold text-theme-text-primary">Search TMDB</h2>
                            <p class="mt-1 text-sm text-theme-text-secondary">Search for movies and TV shows to add to your library.</p>
                        </div>
                        <form wire:submit="search" class="flex gap-3">
                            <input
                                wire:model="query"
                                type="text"
                                placeholder="Search for a movie or TV show..."
                                class="flex-1 rounded-md border-0 py-2 px-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                                autofocus
                            >
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                                Search
                            </button>
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
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                        @foreach($searchResults as $result)
                            <button
                                wire:click="selectResult({{ $result['tmdb_id'] }}, '{{ $result['media_type'] }}')"
                                wire:loading.attr="disabled"
                                class="group relative bg-theme-card-bg rounded-lg shadow-sm ring-1 ring-theme-border-primary overflow-hidden hover:shadow-md hover:ring-theme-accent-primary transition-all text-left"
                            >
                                {{-- Type badge --}}
                                <div class="absolute top-2 right-2 z-10">
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $result['media_type'] === 'tv' ? 'bg-sky-500/90 text-white' : 'bg-amber-500/90 text-white' }}">
                                        {{ $result['media_type'] === 'tv' ? 'TV' : 'Movie' }}
                                    </span>
                                </div>
                                <div class="aspect-[2/3] bg-theme-bg-tertiary flex items-center justify-center">
                                    @if($result['poster_url'])
                                        <img src="{{ $result['poster_url'] }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <svg class="h-10 w-10 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="p-2">
                                    <h3 class="text-xs font-medium text-theme-text-primary line-clamp-2 leading-tight">{{ $result['title'] }}</h3>
                                    @if($result['year'])
                                        <p class="mt-0.5 text-[10px] text-theme-text-muted">{{ $result['year'] }}</p>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($totalPages > 1)
                        <div class="mt-6 flex justify-center gap-2">
                            @if($currentPage > 1)
                                <button wire:click="loadPage({{ $currentPage - 1 }})" class="rounded-md btn-secondary px-3 py-1.5 text-sm ring-1 ring-inset shadow-sm">Previous</button>
                            @endif
                            <span class="px-3 py-1.5 text-sm text-theme-text-secondary">Page {{ $currentPage }} of {{ $totalPages }}</span>
                            @if($currentPage < $totalPages)
                                <button wire:click="loadPage({{ $currentPage + 1 }})" class="rounded-md btn-secondary px-3 py-1.5 text-sm ring-1 ring-inset shadow-sm">Next</button>
                            @endif
                        </div>
                    @endif
                @endif

                {{-- Loading overlay --}}
                <div wire:loading wire:target="selectResult" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
                    <div class="bg-theme-card-bg rounded-lg p-6 shadow-xl ring-1 ring-theme-border-primary flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-theme-accent-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-theme-text-primary">Fetching details...</span>
                    </div>
                </div>

            {{-- ===== STEP: CONFIGURE MOVIE ===== --}}
            @elseif($step === 'configure_movie')
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
                            {{-- Top: poster thumbnail + key info --}}
                            <div class="flex gap-4 mb-6">
                                <div class="w-24 flex-shrink-0">
                                    @if($poster_url)
                                        <img src="{{ $poster_url }}" alt="" class="w-full rounded-md object-cover aspect-[2/3]">
                                    @else
                                        <div class="w-full aspect-[2/3] rounded-md bg-theme-bg-tertiary flex items-center justify-center">
                                            <svg class="h-8 w-8 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3.375 19.5h17.25" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="title" class="block text-sm font-medium text-theme-text-primary">Title</label>
                                    <input wire:model="title" type="text" id="title" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                    <div class="mt-3 grid grid-cols-3 gap-3">
                                        <div>
                                            <label for="director" class="block text-xs font-medium text-theme-text-muted">Director</label>
                                            <input wire:model="director" type="text" id="director" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                        <div>
                                            <label for="year" class="block text-xs font-medium text-theme-text-muted">Year</label>
                                            <input wire:model="year" type="number" id="year" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                        <div>
                                            <label for="runtime" class="block text-xs font-medium text-theme-text-muted">Runtime (min)</label>
                                            <input wire:model="runtime_minutes" type="number" id="runtime" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Genres --}}
                            <div class="mb-4">
                                <label for="genres" class="block text-sm font-medium text-theme-text-primary">Genres</label>
                                <input wire:model="genres" type="text" id="genres" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                            </div>

                            {{-- Description --}}
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-theme-text-primary">Description</label>
                                <textarea wire:model="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"></textarea>
                            </div>

                            {{-- Status + Rating row --}}
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
                                    <div class="mt-1 flex items-center gap-1">
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

                            {{-- Actions --}}
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-theme-border-primary">
                                <button wire:click="backToResults" type="button" class="text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Cancel</button>
                                <button wire:click="addMovie" type="button" class="rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm">
                                    Add to Library
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- ===== STEP: CONFIGURE TV ===== --}}
            @elseif($step === 'configure_tv')
                <div class="mb-4">
                    <button wire:click="backToResults" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back to Results
                    </button>
                </div>

                {{-- Show Info --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden mb-6">
                    <div class="p-6">
                        <div class="flex gap-4">
                            <div class="w-24 flex-shrink-0">
                                @if($poster_url)
                                    <img src="{{ $poster_url }}" alt="" class="w-full rounded-md object-cover aspect-[2/3]">
                                @else
                                    <div class="w-full aspect-[2/3] rounded-md bg-theme-bg-tertiary flex items-center justify-center">
                                        <svg class="h-8 w-8 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3.375 19.5h17.25" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                            <h2 class="text-xl font-bold text-theme-text-primary">{{ $title }}</h2>
                            <div class="mt-1 flex items-center gap-2 text-sm text-theme-text-secondary">
                                @if($year)<span>{{ $year }}</span>@endif
                                @if($genres)<span class="text-theme-text-muted">{{ $genres }}</span>@endif
                            </div>
                            @if($description)
                                <p class="mt-3 text-sm text-theme-text-secondary line-clamp-3">{{ $description }}</p>
                            @endif

                            {{-- Show status/rating --}}
                            <div class="mt-4 flex flex-wrap items-center gap-4">
                                <div>
                                    <label for="show_status" class="block text-xs font-medium text-theme-text-muted">Show Status</label>
                                    <select wire:model="status" id="show_status" class="mt-1 rounded-md border-0 py-1 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-theme-text-muted">Show Rating</label>
                                    <div class="mt-1 flex items-center gap-0.5">
                                        @for($i = 1; $i <= 10; $i++)
                                            <button
                                                wire:click="$set('rating', {{ $rating === $i ? 'null' : $i }})"
                                                type="button"
                                                class="h-7 w-7 rounded text-xs font-bold transition-colors {{ $i <= ($rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                            >{{ $i }}</button>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                {{-- Seasons List --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg">
                    <div class="px-6 py-4 border-b border-theme-border-primary flex items-center justify-between">
                        <h3 class="text-base font-semibold text-theme-text-primary">Seasons</h3>
                        @if(!empty($loadedEpisodes))
                            <button wire:click="goToSelectEpisodes" class="rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm">
                                Select Episodes
                            </button>
                        @endif
                    </div>
                    <div class="divide-y divide-theme-border-primary">
                        @foreach($seasons as $season)
                            <div class="px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-theme-text-primary">{{ $season['name'] }}</span>
                                    <span class="text-xs text-theme-text-muted">{{ $season['episode_count'] }} episodes</span>
                                </div>
                                @if(isset($loadedEpisodes[$season['season_number']]))
                                    <span class="inline-flex items-center gap-1 text-xs text-theme-success">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Loaded
                                    </span>
                                @else
                                    <button
                                        wire:click="loadSeasonEpisodes({{ $season['season_number'] }})"
                                        wire:loading.attr="disabled"
                                        wire:target="loadSeasonEpisodes({{ $season['season_number'] }})"
                                        class="rounded-md btn-secondary px-3 py-1.5 text-xs font-medium ring-1 ring-inset shadow-sm"
                                    >
                                        <span wire:loading.remove wire:target="loadSeasonEpisodes({{ $season['season_number'] }})">Load Episodes</span>
                                        <span wire:loading wire:target="loadSeasonEpisodes({{ $season['season_number'] }})">Loading...</span>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Loading overlay --}}
                <div wire:loading wire:target="loadSeasonEpisodes" class="fixed inset-0 bg-black/20 z-50"></div>

            {{-- ===== STEP: SELECT EPISODES ===== --}}
            @elseif($step === 'select_episodes')
                <div class="mb-4">
                    <button wire:click="backToConfigureTV" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back to Show
                    </button>
                </div>

                {{-- Summary bar --}}
                <div class="sticky top-0 z-10 bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4 text-sm">
                        <span class="font-medium text-theme-text-primary">{{ $title }}</span>
                        <span class="text-theme-text-secondary">{{ $summary['selected'] }} selected</span>
                        @if($summary['watched'] > 0)
                            <span class="text-theme-status-watched">{{ $summary['watched'] }} watched</span>
                        @endif
                        @if($summary['watchlist'] > 0)
                            <span class="text-theme-status-watchlist">{{ $summary['watchlist'] }} to watchlist</span>
                        @endif
                    </div>
                    <button
                        wire:click="importTVShow"
                        wire:loading.attr="disabled"
                        @if($summary['selected'] === 0) disabled @endif
                        class="rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="importTVShow">Import {{ $summary['selected'] }} Episode(s)</span>
                        <span wire:loading wire:target="importTVShow">Importing...</span>
                    </button>
                </div>

                {{-- Episodes by season --}}
                <div class="space-y-4">
                    @foreach($loadedEpisodes as $seasonNum => $episodes)
                        <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden">
                            {{-- Season header --}}
                            <div class="px-4 py-3 bg-theme-bg-tertiary border-b border-theme-border-primary flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <button
                                        wire:click="selectAllSeason({{ $seasonNum }})"
                                        type="button"
                                        class="inline-flex items-center gap-2 text-sm font-semibold text-theme-text-primary hover:text-theme-accent-primary"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded"
                                            @if($this->isSeasonFullySelected($seasonNum)) checked @endif
                                            {{-- Read-only, click handled by parent button --}}
                                            onclick="return false;"
                                        >
                                        Season {{ $seasonNum }}
                                    </button>
                                    <span class="text-xs text-theme-text-muted">{{ count($episodes) }} episodes</span>
                                </div>
                                <button
                                    wire:click="markSeasonWatched({{ $seasonNum }})"
                                    type="button"
                                    class="rounded-md btn-secondary px-2.5 py-1 text-xs font-medium ring-1 ring-inset shadow-sm"
                                >
                                    Mark All Watched
                                </button>
                            </div>

                            {{-- Episode rows --}}
                            <div class="divide-y divide-theme-border-primary">
                                @foreach($episodes as $ep)
                                    @php
                                        $epKey = "S{$seasonNum}E{$ep['episode_number']}";
                                        $isSelected = isset($selectedEpisodes[$epKey]);
                                        $isWatched = isset($watchedEpisodes[$epKey]);
                                        $isDuplicate = $this->isEpisodeDuplicate($seasonNum, $ep['episode_number']);
                                    @endphp
                                    <div class="px-4 py-2.5 flex items-center gap-3 {{ $isDuplicate ? 'opacity-50' : '' }}">
                                        {{-- Select checkbox --}}
                                        <button
                                            wire:click="toggleEpisode({{ $seasonNum }}, {{ $ep['episode_number'] }})"
                                            type="button"
                                            @if($isDuplicate) disabled @endif
                                            class="flex-shrink-0"
                                        >
                                            <input type="checkbox" class="h-4 w-4 rounded" @if($isSelected) checked @endif @if($isDuplicate) disabled @endif onclick="return false;">
                                        </button>

                                        {{-- Episode badge --}}
                                        <span class="flex-shrink-0 inline-flex items-center rounded px-1.5 py-0.5 text-xs font-bold {{ $isSelected ? 'bg-sky-500/20 text-sky-400' : 'bg-theme-bg-tertiary text-theme-text-muted' }}">
                                            E{{ str_pad((string)$ep['episode_number'], 2, '0', STR_PAD_LEFT) }}
                                        </span>

                                        {{-- Episode name --}}
                                        <span class="flex-1 text-sm text-theme-text-primary truncate">
                                            {{ $ep['name'] }}
                                            @if($isDuplicate)
                                                <span class="text-xs text-theme-text-muted">(already in library)</span>
                                            @endif
                                        </span>

                                        {{-- Air date --}}
                                        @if($ep['air_date'])
                                            <span class="hidden sm:inline text-xs text-theme-text-muted flex-shrink-0">{{ $ep['air_date'] }}</span>
                                        @endif

                                        {{-- Watched toggle --}}
                                        @unless($isDuplicate)
                                            <button
                                                wire:click="toggleEpisodeWatched({{ $seasonNum }}, {{ $ep['episode_number'] }})"
                                                type="button"
                                                class="flex-shrink-0 rounded px-2 py-0.5 text-xs font-medium transition-colors {{ $isWatched ? 'bg-theme-status-watched-bg text-theme-status-watched' : 'bg-theme-bg-tertiary text-theme-text-muted hover:text-theme-text-secondary' }}"
                                            >
                                                {{ $isWatched ? 'Watched' : 'Watchlist' }}
                                            </button>
                                        @endunless
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Loading overlay --}}
                <div wire:loading wire:target="importTVShow" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
                    <div class="bg-theme-card-bg rounded-lg p-6 shadow-xl ring-1 ring-theme-border-primary flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-theme-accent-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-theme-text-primary">Importing episodes...</span>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
