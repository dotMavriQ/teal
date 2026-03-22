<div>
    {{-- Header --}}
    <header class="bg-theme-bg-primary shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
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
                            <li><span class="text-theme-text-tertiary">Board Games</span></li>
                        </ol>
                    </nav>
                    <h1 class="mt-1 text-2xl font-bold text-theme-text-primary">Board Games</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('board-games.discover') }}" class="inline-flex items-center gap-1.5 rounded-md btn-secondary px-3 py-2 text-sm font-medium shadow-sm ring-1 ring-inset">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="hidden sm:inline">Discover</span>
                    </a>
                    <a href="{{ route('board-games.create') }}" class="inline-flex items-center gap-1.5 rounded-md btn-primary px-3 py-2 text-sm font-medium shadow-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">Add Board Game</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Flash Message --}}
            @if (session()->has('message'))
                <div class="mb-4 rounded-md bg-theme-success-bg p-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-theme-success" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm font-medium text-theme-success-text">{{ session('message') }}</p>
                </div>
            @endif

            {{-- Toolbar --}}
            <div class="bg-theme-card-bg rounded-lg shadow-sm ring-1 ring-theme-border-primary p-4 mb-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    {{-- Left: Search & Filters --}}
                    <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap">
                        {{-- Search --}}
                        <div class="relative flex-1 max-w-sm">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-theme-text-muted" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                            <input
                                wire:model.live.debounce.300ms="search"
                                type="search"
                                placeholder="Search title, designer..."
                                class="block w-full rounded-md border-0 py-1.5 pl-9 pr-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                            >
                        </div>

                        {{-- Status Filter --}}
                        <select
                            wire:model.live="status"
                            class="rounded-md border-0 py-1.5 pl-3 pr-8 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"
                        >
                            <option value="">All statuses</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                            @endforeach
                        </select>

                        {{-- Ownership Filter --}}
                        <select
                            wire:model.live="ownership"
                            class="rounded-md border-0 py-1.5 pl-3 pr-8 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"
                        >
                            <option value="">All ownership</option>
                            @foreach($ownershipStatuses as $ownershipOption)
                                <option value="{{ $ownershipOption->value }}">{{ $ownershipOption->label() }}</option>
                            @endforeach
                        </select>

                        {{-- Genre Filter --}}
                        <select
                            wire:model.live="genre"
                            class="rounded-md border-0 py-1.5 pl-3 pr-8 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"
                        >
                            <option value="">All genres</option>
                            @foreach($allGenres as $genreOption)
                                <option value="{{ $genreOption }}">{{ $genreOption }}</option>
                            @endforeach
                        </select>

                        {{-- Sort --}}
                        <select
                            wire:model.live="sortBy"
                            class="rounded-md border-0 py-1.5 pl-3 pr-8 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"
                        >
                            <optgroup label="Metadata">
                                <option value="title">Title</option>
                                <option value="year_published">Year Published</option>
                            </optgroup>
                            <optgroup label="Your Data">
                                <option value="rating">Your Rating</option>
                                <option value="plays">Plays</option>
                                <option value="updated_at">Recently Updated</option>
                            </optgroup>
                        </select>

                        {{-- Sort Direction --}}
                        <button
                            wire:click="$set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')"
                            class="inline-flex items-center justify-center rounded-md p-1.5 text-theme-text-secondary ring-1 ring-inset ring-theme-border-secondary hover:bg-theme-bg-hover"
                            title="{{ $sortDirection === 'asc' ? 'Ascending' : 'Descending' }}"
                        >
                            @if($sortDirection === 'asc')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                </svg>
                            @else
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                                </svg>
                            @endif
                        </button>
                    </div>

                    {{-- Right: View Toggle & Bulk Actions --}}
                    <div class="flex items-center gap-2">
                        {{-- Bulk Actions --}}
                        @if(count($selected) > 0)
                            <span class="text-sm text-theme-text-secondary">{{ count($selected) }} selected</span>
                            <button
                                wire:click="deleteSelected"
                                wire:confirm="Delete {{ count($selected) }} board game(s)?"
                                class="inline-flex items-center gap-1 rounded-md btn-danger px-2.5 py-1.5 text-sm font-medium"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        @endif

                        <div class="h-6 w-px bg-theme-border-primary"></div>

                        {{-- View Toggle --}}
                        <div class="inline-flex rounded-md shadow-sm">
                            <button
                                wire:click="setViewMode('gallery')"
                                class="inline-flex items-center px-2.5 py-1.5 text-sm font-medium rounded-l-md border border-theme-border-secondary {{ $viewMode === 'gallery' ? 'bg-theme-bg-active text-theme-text-primary' : 'bg-theme-card-bg text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                title="Gallery"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </button>
                            <button
                                wire:click="setViewMode('list')"
                                class="inline-flex items-center px-2.5 py-1.5 text-sm font-medium rounded-r-md border-t border-r border-b border-theme-border-secondary -ml-px {{ $viewMode === 'list' ? 'bg-theme-bg-active text-theme-text-primary' : 'bg-theme-card-bg text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                title="List"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Select All --}}
                @if($boardGames->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-theme-border-primary flex items-center gap-2">
                        <input
                            wire:model.live="selectAll"
                            type="checkbox"
                            id="selectAll"
                            class="h-4 w-4 rounded"
                        >
                        <label for="selectAll" class="text-sm text-theme-text-secondary">Select all ({{ $boardGames->total() }} board games)</label>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            @if($boardGames->isEmpty())
                <div class="text-center py-16">
                    {{-- Dice SVG icon --}}
                    <svg class="mx-auto h-12 w-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-theme-text-primary">No board games yet</h3>
                    <p class="mt-1 text-sm text-theme-text-secondary">Get started by adding a board game or discovering new ones.</p>
                    <div class="mt-6 flex justify-center gap-3">
                        <a href="{{ route('board-games.create') }}" class="rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm">
                            Add Board Game
                        </a>
                        <a href="{{ route('board-games.discover') }}" class="rounded-md btn-secondary px-4 py-2 text-sm font-medium shadow-sm ring-1 ring-inset">
                            Discover
                        </a>
                    </div>
                </div>
            @else
                <div wire:loading.class="opacity-50" wire:target="gotoPage, previousPage, nextPage, search, status, ownership, genre, sortBy, sortDirection, setViewMode, boardGames">
                    @if($viewMode === 'gallery')
                        {{-- Gallery View --}}
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                            @foreach($boardGames as $boardGame)
                                <article wire:key="board-game-{{ $boardGame->id }}" class="group relative bg-theme-card-bg rounded-lg shadow-sm ring-1 ring-theme-border-primary overflow-hidden hover:shadow-md transition-shadow">
                                    <div class="absolute top-2 left-2 z-10">
                                        <input wire:model.live="selected" type="checkbox" value="{{ $boardGame->id }}" class="h-4 w-4 rounded border-theme-border-secondary text-theme-accent-primary bg-white/90 shadow-sm">
                                    </div>
                                    @if($boardGame->rating)
                                        <div class="absolute top-2 right-2 z-10 flex items-center gap-0.5 bg-theme-card-bg/95 rounded px-1.5 py-0.5 border border-theme-border-primary shadow-sm">
                                            <span class="text-xs font-bold text-theme-star-filled">{{ $boardGame->rating }}/10</span>
                                        </div>
                                    @endif
                                    <a href="{{ route('board-games.show', $boardGame) }}" class="block">
                                        <div class="aspect-[2/3] bg-theme-bg-tertiary flex items-center justify-center">
                                            @if($boardGame->cover_url)
                                                <img src="{{ $boardGame->cover_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                            @else
                                                <svg class="h-10 w-10 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="p-2">
                                            <h3 class="text-xs font-medium text-theme-text-primary line-clamp-2 leading-tight">{{ $boardGame->title }}</h3>
                                            @if($boardGame->designer)
                                                <p class="mt-0.5 text-xs text-theme-text-secondary truncate">{{ $boardGame->designer }}</p>
                                            @endif
                                            <div class="mt-1">
                                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium
                                                    @switch($boardGame->status->value)
                                                        @case('want_to_play') bg-theme-status-want-to-play-bg text-theme-status-want-to-play @break
                                                        @case('playing') bg-theme-status-playing-bg text-theme-status-playing @break
                                                        @case('played') bg-theme-status-played-bg text-theme-status-played @break
                                                        @default bg-theme-bg-tertiary text-theme-text-muted
                                                    @endswitch
                                                ">{{ $boardGame->status->label() }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @else
                        {{-- List View --}}
                        <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-theme-border-primary">
                                    <thead class="bg-theme-bg-tertiary">
                                        <tr>
                                            <th scope="col" class="w-10 px-3 py-3"></th>
                                            <th scope="col" class="w-20 px-2 py-3"></th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-theme-text-tertiary uppercase tracking-wider">
                                                <button wire:click="sort('title')" class="group inline-flex items-center gap-1 hover:text-theme-text-primary">
                                                    Title
                                                    <span class="flex-none rounded {{ $sortBy === 'title' ? 'text-theme-text-primary' : 'text-theme-text-muted invisible group-hover:visible' }}">
                                                        @if($sortBy === 'title' && $sortDirection === 'asc')
                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                                                        @else
                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                        @endif
                                                    </span>
                                                </button>
                                            </th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-theme-text-tertiary uppercase tracking-wider hidden md:table-cell">Designer</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-theme-text-tertiary uppercase tracking-wider hidden lg:table-cell">Players</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-theme-text-tertiary uppercase tracking-wider hidden md:table-cell">
                                                <button wire:click="sort('rating')" class="group inline-flex items-center gap-1 hover:text-theme-text-primary">
                                                    Rating
                                                    <span class="flex-none rounded {{ $sortBy === 'rating' ? 'text-theme-text-primary' : 'text-theme-text-muted invisible group-hover:visible' }}">
                                                        @if($sortBy === 'rating' && $sortDirection === 'asc')
                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                                                        @else
                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                        @endif
                                                    </span>
                                                </button>
                                            </th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-theme-text-tertiary uppercase tracking-wider hidden lg:table-cell">
                                                <button wire:click="sort('plays')" class="group inline-flex items-center gap-1 hover:text-theme-text-primary">
                                                    Plays
                                                    <span class="flex-none rounded {{ $sortBy === 'plays' ? 'text-theme-text-primary' : 'text-theme-text-muted invisible group-hover:visible' }}">
                                                        @if($sortBy === 'plays' && $sortDirection === 'asc')
                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                                                        @else
                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                        @endif
                                                    </span>
                                                </button>
                                            </th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-theme-text-tertiary uppercase tracking-wider hidden sm:table-cell">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-theme-card-bg divide-y divide-theme-border-primary">
                                        @foreach($boardGames as $boardGame)
                                            <tr wire:key="board-game-{{ $boardGame->id }}" class="hover:bg-theme-bg-hover">
                                                <td class="px-3 py-2">
                                                    <input wire:model.live="selected" type="checkbox" value="{{ $boardGame->id }}" class="h-4 w-4 rounded border-theme-border-secondary text-theme-accent-primary">
                                                </td>
                                                <td class="px-2 py-2">
                                                    <a href="{{ route('board-games.show', $boardGame) }}" class="block">
                                                        <div class="w-12 h-18 bg-theme-bg-tertiary rounded overflow-hidden flex-shrink-0">
                                                            @if($boardGame->cover_url)
                                                                <img src="{{ $boardGame->cover_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                                            @else
                                                                <div class="h-full w-full flex items-center justify-center">
                                                                    <svg class="h-5 w-5 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </a>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <a href="{{ route('board-games.show', $boardGame) }}" class="text-sm font-medium text-theme-text-primary hover:text-theme-accent-primary">
                                                        {{ Str::limit($boardGame->title, 50) }}
                                                    </a>
                                                    @if($boardGame->year_published)
                                                        <p class="text-xs text-theme-text-muted">{{ $boardGame->year_published }}</p>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-sm text-theme-text-secondary hidden md:table-cell">
                                                    {{ $boardGame->designer ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2 text-sm text-theme-text-secondary hidden lg:table-cell">
                                                    @if($boardGame->min_players || $boardGame->max_players)
                                                        @if($boardGame->min_players === $boardGame->max_players)
                                                            {{ $boardGame->min_players }}
                                                        @else
                                                            {{ $boardGame->min_players }}–{{ $boardGame->max_players }}
                                                        @endif
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 hidden md:table-cell">
                                                    @if($boardGame->rating)
                                                        <span class="text-sm font-medium text-theme-star-filled">{{ $boardGame->rating }}/10</span>
                                                    @else
                                                        <span class="text-xs text-theme-text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-sm text-theme-text-tertiary hidden lg:table-cell">
                                                    {{ $boardGame->plays ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2 hidden sm:table-cell">
                                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium w-fit
                                                        @switch($boardGame->status->value)
                                                            @case('want_to_play') bg-theme-status-want-to-play-bg text-theme-status-want-to-play @break
                                                            @case('playing') bg-theme-status-playing-bg text-theme-status-playing @break
                                                            @case('played') bg-theme-status-played-bg text-theme-status-played @break
                                                            @default bg-theme-bg-tertiary text-theme-text-muted
                                                        @endswitch
                                                    ">{{ $boardGame->status->label() }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $boardGames->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
