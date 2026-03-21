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
                        <a href="{{ route('games.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Games</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li><span class="text-theme-text-tertiary">Discover Games</span></li>
                </ol>
            </nav>
            <h1 class="mt-1 text-2xl font-bold text-theme-text-primary">Discover Games</h1>
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
                            <svg class="mx-auto h-12 w-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z" />
                            </svg>
                            <h2 class="mt-2 text-lg font-semibold text-theme-text-primary">Discover Games</h2>
                            <p class="mt-1 text-sm text-theme-text-secondary">Search IGDB to find games and add them to your library.</p>
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
                            <div class="flex justify-center">
                                <select
                                    wire:model="platformFilter"
                                    class="rounded-md border-0 py-1.5 pl-3 pr-8 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"
                                >
                                    @foreach($platformOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
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
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                        @foreach($searchResults as $index => $result)
                            <button
                                wire:click="selectResult({{ $index }})"
                                wire:loading.attr="disabled"
                                class="group relative bg-theme-card-bg rounded-lg shadow-sm ring-1 ring-theme-border-primary overflow-hidden hover:shadow-md hover:ring-theme-accent-primary transition-all text-left {{ $this->isResultDuplicate($result) ? 'opacity-50' : '' }}"
                            >
                                {{-- Duplicate badge --}}
                                @if($this->isResultDuplicate($result))
                                    <div class="absolute top-2 right-2 z-10">
                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase bg-theme-text-muted text-theme-text-inverted">
                                            In Library
                                        </span>
                                    </div>
                                @endif
                                <div class="aspect-[2/3] bg-theme-bg-tertiary flex items-center justify-center">
                                    @if($result['cover_url'])
                                        <img src="{{ $result['cover_url'] }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <svg class="h-10 w-10 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="p-2">
                                    <h3 class="text-xs font-medium text-theme-text-primary line-clamp-2 leading-tight">{{ $result['title'] }}</h3>
                                    @if($result['developer'])
                                        <p class="mt-0.5 text-[10px] text-theme-text-secondary line-clamp-1">{{ $result['developer'] }}</p>
                                    @endif
                                    @if($result['release_date'])
                                        <p class="mt-0.5 text-[10px] text-theme-text-muted">{{ substr($result['release_date'], 0, 4) }}</p>
                                    @endif
                                    @if(!empty($result['platforms']))
                                        <div class="mt-1 flex flex-wrap gap-0.5">
                                            @foreach(array_slice($result['platforms'], 0, 2) as $plat)
                                                <span class="inline-block rounded px-1 py-0.5 text-[8px] font-medium bg-theme-bg-tertiary text-theme-text-muted border border-theme-border-primary">{{ $plat }}</span>
                                            @endforeach
                                            @if(count($result['platforms']) > 2)
                                                <span class="inline-block rounded px-1 py-0.5 text-[8px] text-theme-text-muted">+{{ count($result['platforms']) - 2 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6 flex justify-center gap-2">
                        @if($currentPage > 1)
                            <button wire:click="loadPage({{ $currentPage - 1 }})" class="rounded-md btn-secondary px-3 py-1.5 text-sm ring-1 ring-inset shadow-sm">Previous</button>
                        @endif
                        <span class="px-3 py-1.5 text-sm text-theme-text-secondary">Page {{ $currentPage }}</span>
                        @if($hasMorePages)
                            <button wire:click="loadPage({{ $currentPage + 1 }})" class="rounded-md btn-secondary px-3 py-1.5 text-sm ring-1 ring-inset shadow-sm">Next</button>
                        @endif
                    </div>
                @endif

                {{-- Loading overlay --}}
                <div wire:loading wire:target="selectResult" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="title" class="block text-sm font-medium text-theme-text-primary">Title</label>
                                    <input wire:model="title" type="text" id="title" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                    <div class="mt-3 grid grid-cols-2 gap-3">
                                        <div>
                                            <label for="developer" class="block text-xs font-medium text-theme-text-muted">Developer</label>
                                            <input wire:model="developer" type="text" id="developer" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                        <div>
                                            <label for="publisher" class="block text-xs font-medium text-theme-text-muted">Publisher</label>
                                            <input wire:model="publisher" type="text" id="publisher" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-3">
                                        <div>
                                            <label for="genre" class="block text-xs font-medium text-theme-text-muted">Genre</label>
                                            <input wire:model="genre" type="text" id="genre" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                        <div>
                                            <label for="release_date" class="block text-xs font-medium text-theme-text-muted">Release Date</label>
                                            <input wire:model="release_date" type="text" id="release_date" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary" placeholder="YYYY-MM-DD">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Platforms you own it on --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-theme-text-primary mb-2">Platforms you own it on</label>

                                {{-- IGDB platforms as checkboxes --}}
                                @if(!empty($availablePlatforms))
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @foreach($availablePlatforms as $platform)
                                            <button
                                                wire:click="togglePlatform('{{ addslashes($platform) }}')"
                                                type="button"
                                                class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium transition-colors
                                                    {{ in_array($platform, $selectedPlatforms)
                                                        ? 'bg-theme-accent-primary text-theme-text-inverted'
                                                        : 'bg-theme-bg-secondary text-theme-text-secondary ring-1 ring-theme-border-primary hover:bg-theme-bg-hover' }}"
                                            >
                                                {{ $platform }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Custom platform input --}}
                                <div class="flex gap-2">
                                    <input
                                        wire:model="customPlatformInput"
                                        wire:keydown.enter.prevent="addCustomPlatform"
                                        type="text"
                                        placeholder="Add custom (e.g. PC (Steam), PC (GOG))"
                                        class="flex-1 rounded-md border-0 py-1.5 px-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                                    >
                                    <button type="button" wire:click="addCustomPlatform" class="rounded-md btn-secondary px-3 py-1.5 text-sm font-medium ring-1 ring-inset shadow-sm">Add</button>
                                </div>

                                {{-- Show selected platforms that aren't in the IGDB list (custom ones) --}}
                                @php $customSelected = array_filter($selectedPlatforms, fn($p) => !in_array($p, $availablePlatforms)); @endphp
                                @if(!empty($customSelected))
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($selectedPlatforms as $index => $platform)
                                            @if(!in_array($platform, $availablePlatforms))
                                                <span class="inline-flex items-center gap-1 rounded-full bg-theme-accent-primary text-theme-text-inverted px-3 py-1 text-sm font-medium">
                                                    {{ $platform }}
                                                    <button type="button" wire:click="removeSelectedPlatform({{ $index }})" class="ml-1 hover:opacity-75 focus:outline-none" aria-label="Remove {{ $platform }}">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Description --}}
                            <div class="mb-4">
                                <label for="summary" class="block text-sm font-medium text-theme-text-primary">Description</label>
                                <textarea wire:model="summary" id="summary" rows="4" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"></textarea>
                            </div>

                            {{-- Status + Ownership + Rating --}}
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
                                    <label for="ownership" class="block text-sm font-medium text-theme-text-primary">Ownership</label>
                                    <select wire:model="ownership" id="ownership" class="mt-1 block rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        @foreach($ownershipStatuses as $ownershipOption)
                                            <option value="{{ $ownershipOption->value }}">{{ $ownershipOption->label() }}</option>
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
                                <button wire:click="addGame" type="button" class="rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm">
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
