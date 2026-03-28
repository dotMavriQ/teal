<div>
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
                        <a href="{{ route('listening.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Listening</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li>
                        <a href="{{ route('albums.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Collection</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li><span class="text-theme-text-tertiary">Discover</span></li>
                </ol>
            </nav>
            <h1 class="mt-1 text-2xl font-bold text-theme-text-primary">Discover Albums on Discogs</h1>
        </div>
    </header>

    <main class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
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
                <div class="mb-4 rounded-md bg-theme-danger-bg p-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-theme-danger" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm font-medium text-theme-danger">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Step: Search --}}
            @if($step === 'search')
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-theme-text-primary mb-4">Search Discogs</h2>
                    <form wire:submit="search" class="flex gap-3">
                        <div class="flex-1">
                            <input
                                wire:model="searchQuery"
                                type="text"
                                placeholder="Search by artist, album title..."
                                class="block w-full rounded-md border-0 py-2 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary sm:text-sm"
                                autofocus
                            >
                        </div>
                        <button type="submit" class="rounded-md btn-primary px-4 py-2 text-sm font-semibold shadow-sm">
                            Search
                        </button>
                    </form>
                    <div wire:loading wire:target="search" class="mt-4 text-sm text-theme-text-muted">
                        Searching Discogs...
                    </div>
                </div>
            @endif

            {{-- Step: Results --}}
            @if($step === 'results')
                <div class="mb-4">
                    <button wire:click="back" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Back to search
                    </button>
                </div>

                @if(empty($results))
                    <div class="text-center py-12 bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                        <svg class="mx-auto h-12 w-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="2" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-theme-text-primary">No results found</h3>
                        <p class="mt-1 text-sm text-theme-text-secondary">Try a different search term.</p>
                    </div>
                @else
                    <h2 class="text-lg font-semibold text-theme-text-primary mb-4">{{ count($results) }} results for "{{ $searchQuery }}"</h2>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                        @foreach($results as $index => $result)
                            <button
                                wire:click="selectRelease({{ $index }})"
                                wire:loading.attr="disabled"
                                class="group text-left bg-theme-card-bg rounded-lg shadow-sm ring-1 ring-theme-border-primary overflow-hidden hover:shadow-md hover:ring-theme-accent-primary transition-all"
                            >
                                <div class="aspect-square bg-theme-bg-tertiary flex items-center justify-center">
                                    @if(!empty($result['cover_url']))
                                        <img src="{{ $result['cover_url'] }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <svg class="h-10 w-10 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="2" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="p-2">
                                    <h3 class="text-xs font-medium text-theme-text-primary line-clamp-2 leading-tight">{{ $result['title'] }}</h3>
                                    @if($result['year'])
                                        <p class="mt-0.5 text-xs text-theme-text-muted">{{ $result['year'] }}</p>
                                    @endif
                                    @if($result['format'])
                                        <p class="mt-0.5 text-xs text-theme-text-muted">{{ $result['format'] }}</p>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                    <div wire:loading wire:target="selectRelease" class="mt-4 text-center text-sm text-theme-text-muted">
                        Fetching release details...
                    </div>
                @endif
            @endif

            {{-- Step: Configure --}}
            @if($step === 'configure' && $selectedRelease)
                <div class="mb-4">
                    <button wire:click="back" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Back to results
                    </button>
                </div>

                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl overflow-hidden">
                    {{-- Album Preview --}}
                    <div class="p-6 flex gap-6">
                        <div class="w-32 h-32 flex-shrink-0 rounded-lg overflow-hidden bg-theme-bg-tertiary">
                            @if(!empty($selectedRelease['cover_url']))
                                <img src="{{ $selectedRelease['cover_url'] }}" alt="" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <svg class="h-10 w-10 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="2" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl font-bold text-theme-text-primary">{{ $selectedRelease['title'] }}</h2>
                            <p class="text-sm text-theme-text-secondary">{{ $selectedRelease['artist'] }}</p>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-theme-text-muted">
                                @if(!empty($selectedRelease['year']))
                                    <span>{{ $selectedRelease['year'] }}</span>
                                @endif
                                @if(!empty($selectedRelease['format']))
                                    <span>{{ $selectedRelease['format'] }}</span>
                                @endif
                                @if(!empty($selectedRelease['label']))
                                    <span>{{ $selectedRelease['label'] }}</span>
                                @endif
                                @if(!empty($selectedRelease['country']))
                                    <span>{{ $selectedRelease['country'] }}</span>
                                @endif
                            </div>
                            @if(!empty($selectedRelease['genre']))
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($selectedRelease['genre'] as $g)
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium bg-theme-bg-tertiary text-theme-text-secondary">{{ $g }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Tracklist Preview --}}
                    @if(!empty($selectedRelease['tracklist']))
                        <div class="px-6 pb-4">
                            <h3 class="text-sm font-medium text-theme-text-secondary mb-2">Tracklist ({{ count($selectedRelease['tracklist']) }} tracks)</h3>
                            <ol class="space-y-0.5 max-h-48 overflow-y-auto">
                                @foreach($selectedRelease['tracklist'] as $track)
                                    <li class="flex items-center gap-2 py-1 text-xs border-b border-theme-border-primary last:border-0">
                                        <span class="w-6 text-right text-theme-text-muted">{{ $track['position'] }}</span>
                                        <span class="flex-1 text-theme-text-primary">{{ $track['title'] }}</span>
                                        @if(!empty($track['duration']))
                                            <span class="text-theme-text-muted">{{ $track['duration'] }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    {{-- Configure Options --}}
                    <div class="border-t border-theme-border-primary p-6">
                        <h3 class="text-base font-semibold text-theme-text-primary mb-4">Add to your collection</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            {{-- Status --}}
                            <div>
                                <label for="config-status" class="block text-sm font-medium text-theme-text-primary">Status</label>
                                <select wire:model="status" id="config-status" class="mt-1 block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary sm:text-sm">
                                    @foreach($statuses as $statusOption)
                                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Ownership --}}
                            <div>
                                <label for="config-ownership" class="block text-sm font-medium text-theme-text-primary">Ownership</label>
                                <select wire:model="ownership" id="config-ownership" class="mt-1 block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary sm:text-sm">
                                    @foreach($ownershipStatuses as $ownershipOption)
                                        <option value="{{ $ownershipOption->value }}">{{ $ownershipOption->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Rating --}}
                            <div>
                                <label class="block text-sm font-medium text-theme-text-primary">Rating</label>
                                <div class="mt-1 flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button
                                            wire:click="$set('rating', {{ $rating === $i ? 'null' : $i }})"
                                            type="button"
                                            class="focus:outline-none"
                                        >
                                            <svg class="h-6 w-6 {{ $i <= ($rating ?? 0) ? 'text-theme-star-filled' : 'text-theme-star-empty hover:text-theme-star-filled/50' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="mt-4">
                            <label for="config-notes" class="block text-sm font-medium text-theme-text-primary">Notes</label>
                            <textarea wire:model="notes" id="config-notes" rows="2" placeholder="Personal notes..." class="mt-1 block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary sm:text-sm"></textarea>
                        </div>

                        {{-- Save --}}
                        <div class="mt-6 flex justify-end">
                            <button
                                wire:click="save"
                                wire:loading.attr="disabled"
                                class="rounded-md btn-primary px-4 py-2 text-sm font-semibold shadow-sm"
                            >
                                <span wire:loading.remove wire:target="save">Add to Collection</span>
                                <span wire:loading wire:target="save">Adding...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
