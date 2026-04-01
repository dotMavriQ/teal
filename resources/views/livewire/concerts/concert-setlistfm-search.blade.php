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
                        <a href="{{ route('listening.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Listening</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li>
                        <a href="{{ route('concerts.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Live</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li><span class="text-theme-text-tertiary">Discover</span></li>
                </ol>
            </nav>
            <h1 class="mt-1 text-2xl font-bold text-theme-text-primary">Discover Concerts</h1>
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
                <div class="mb-4 rounded-md bg-theme-danger-bg p-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-theme-danger" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm font-medium text-theme-danger">{{ session('error') }}</p>
                </div>
            @endif
            @if (session()->has('duplicate'))
                <div class="mb-4 rounded-md bg-theme-warning-bg p-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-theme-warning" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm font-medium text-theme-warning-text">{{ session('duplicate') }}</p>
                </div>
            @endif

            {{-- ===== STEP: SEARCH ===== --}}
            @if($step === 'search')
                <div class="max-w-2xl mx-auto">
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg p-6">
                        <div class="text-center mb-6">
                            {{-- Microphone icon --}}
                            <svg class="mx-auto h-12 w-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                            </svg>
                            <h2 class="mt-2 text-lg font-semibold text-theme-text-primary">Discover Concerts via setlist.fm</h2>
                            <p class="mt-1 text-sm text-theme-text-secondary">Search for an artist to browse their setlists and add concerts to your library.</p>
                        </div>
                        <div class="flex gap-3">
                            <input
                                wire:model="searchQuery"
                                type="text"
                                placeholder="Search by artist name..."
                                class="flex-1 rounded-md border-0 py-2 px-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                                wire:keydown.enter="searchArtists"
                                autofocus
                            >
                            <button
                                wire:click="searchArtists"
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                                Search
                            </button>
                        </div>
                    </div>
                </div>

            {{-- ===== STEP: ARTISTS ===== --}}
            @elseif($step === 'artists')
                <div class="mb-4 flex items-center justify-between">
                    <button wire:click="back" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        New Search
                    </button>
                    <div class="flex gap-2">
                        <input
                            wire:model="searchQuery"
                            type="text"
                            class="rounded-md border-0 py-1.5 px-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                            wire:keydown.enter="searchArtists"
                        >
                        <button wire:click="searchArtists" type="button" class="rounded-md btn-secondary px-3 py-1.5 text-sm font-medium ring-1 ring-inset shadow-sm">Search</button>
                    </div>
                </div>

                @if(empty($artists))
                    <div class="text-center py-12">
                        <p class="text-theme-text-secondary">No artists found for "{{ $searchQuery }}".</p>
                    </div>
                @else
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden">
                        <ul class="divide-y divide-theme-border-primary">
                            @foreach($artists as $index => $artist)
                                <li wire:key="artist-{{ $index }}">
                                    <button
                                        wire:click="selectArtist('{{ $artist['mbid'] }}', '{{ addslashes($artist['name']) }}')"
                                        wire:loading.attr="disabled"
                                        class="w-full flex items-center gap-4 px-4 py-3 text-left hover:bg-theme-bg-hover transition-colors group"
                                    >
                                        {{-- Microphone placeholder --}}
                                        <div class="w-10 h-10 flex-shrink-0 rounded-full bg-theme-bg-tertiary flex items-center justify-center">
                                            <svg class="h-5 w-5 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                                            </svg>
                                        </div>

                                        {{-- Info --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-theme-text-primary group-hover:text-theme-accent-primary truncate">
                                                {{ $artist['name'] }}
                                            </p>
                                            @if(!empty($artist['disambiguation']))
                                                <p class="text-xs text-theme-text-muted mt-0.5 truncate">{{ $artist['disambiguation'] }}</p>
                                            @endif
                                            @if(!empty($artist['sortName']) && $artist['sortName'] !== $artist['name'])
                                                <p class="text-xs text-theme-text-muted mt-0.5">Sort: {{ $artist['sortName'] }}</p>
                                            @endif
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
                <div wire:loading wire:target="selectArtist" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
                    <div class="bg-theme-card-bg rounded-lg p-6 shadow-xl ring-1 ring-theme-border-primary flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-theme-accent-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-theme-text-primary">Loading setlists...</span>
                    </div>
                </div>

            {{-- ===== STEP: SETLISTS ===== --}}
            @elseif($step === 'setlists')
                <div class="mb-4 flex items-center justify-between">
                    <button wire:click="back" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back to Artists
                    </button>
                    <p class="text-sm text-theme-text-secondary">
                        Setlists for <span class="font-medium text-theme-text-primary">{{ $searchQuery }}</span>
                    </p>
                </div>

                @if(empty($setlists))
                    <div class="text-center py-12">
                        <p class="text-theme-text-secondary">No setlists found for this artist.</p>
                    </div>
                @else
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden">
                        <ul class="divide-y divide-theme-border-primary">
                            @foreach($setlists as $index => $setlist)
                                <li wire:key="setlist-{{ $index }}">
                                    <button
                                        wire:click="selectSetlist({{ $index }})"
                                        class="w-full flex items-center gap-4 px-4 py-3 text-left hover:bg-theme-bg-hover transition-colors group"
                                    >
                                        {{-- Date badge --}}
                                        <div class="flex-shrink-0 w-16 text-center">
                                            @if(!empty($setlist['eventDate']))
                                                @php
                                                    $parts = explode('-', $setlist['eventDate']);
                                                    $day = $parts[0] ?? '';
                                                    $month = $parts[1] ?? '';
                                                    $year = $parts[2] ?? '';
                                                @endphp
                                                <div class="text-xs text-theme-text-muted">{{ $month }}/{{ $year }}</div>
                                                <div class="text-lg font-bold text-theme-text-primary leading-none">{{ $day }}</div>
                                            @else
                                                <div class="text-xs text-theme-text-muted">—</div>
                                            @endif
                                        </div>

                                        {{-- Info --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-theme-text-primary group-hover:text-theme-accent-primary truncate">
                                                {{ $setlist['venue']['name'] ?? 'Unknown Venue' }}
                                            </p>
                                            <p class="text-xs text-theme-text-muted mt-0.5 truncate">
                                                {{ collect([$setlist['venue']['city']['name'] ?? null, $setlist['venue']['city']['country']['name'] ?? null])->filter()->implode(', ') }}
                                            </p>
                                            @if(!empty($setlist['tour']['name']))
                                                <p class="text-xs text-theme-text-secondary mt-0.5">{{ $setlist['tour']['name'] }}</p>
                                            @endif
                                            @php
                                                $songCount = 0;
                                                if (!empty($setlist['sets']['set'])) {
                                                    foreach ($setlist['sets']['set'] as $s) {
                                                        $songCount += count($s['song'] ?? []);
                                                    }
                                                }
                                            @endphp
                                            @if($songCount > 0)
                                                <p class="text-xs text-theme-text-muted mt-0.5">{{ $songCount }} song{{ $songCount !== 1 ? 's' : '' }}</p>
                                            @else
                                                <p class="text-xs text-theme-text-muted mt-0.5">No setlist recorded</p>
                                            @endif
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

            {{-- ===== STEP: CONFIGURE ===== --}}
            @elseif($step === 'configure')
                <div class="mb-4">
                    <button wire:click="back" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back to Setlists
                    </button>
                </div>

                <div class="max-w-3xl mx-auto">
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden">
                        <div class="p-6">
                            {{-- Summary header --}}
                            @if(!empty($selectedSetlist))
                                <div class="mb-6 pb-6 border-b border-theme-border-primary">
                                    <h2 class="text-lg font-semibold text-theme-text-primary">{{ $searchQuery }}</h2>
                                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-theme-text-secondary">
                                        @if(!empty($selectedSetlist['eventDate']))
                                            <span>
                                                <svg class="inline h-4 w-4 mr-0.5 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $selectedSetlist['eventDate'] }}
                                            </span>
                                        @endif
                                        @if(!empty($selectedSetlist['venue']['name']))
                                            <span>
                                                <svg class="inline h-4 w-4 mr-0.5 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ $selectedSetlist['venue']['name'] }}
                                                @if(!empty($selectedSetlist['venue']['city']['name']))
                                                    , {{ $selectedSetlist['venue']['city']['name'] }}
                                                @endif
                                            </span>
                                        @endif
                                        @if(!empty($selectedSetlist['tour']['name']))
                                            <span>
                                                <svg class="inline h-4 w-4 mr-0.5 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                                </svg>
                                                {{ $selectedSetlist['tour']['name'] }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Setlist preview --}}
                                    @if(!empty($selectedSetlist['sets']['set']))
                                        @php
                                            $allSongs = [];
                                            foreach ($selectedSetlist['sets']['set'] as $s) {
                                                foreach ($s['song'] ?? [] as $song) {
                                                    $allSongs[] = $song;
                                                }
                                            }
                                            $preview = array_slice($allSongs, 0, 5);
                                            $remaining = count($allSongs) - count($preview);
                                        @endphp
                                        @if(!empty($preview))
                                            <div class="mt-3">
                                                <p class="text-xs font-medium text-theme-text-muted uppercase tracking-wide mb-1">Setlist preview</p>
                                                <ol class="space-y-0.5">
                                                    @foreach($preview as $i => $song)
                                                        <li class="text-sm text-theme-text-secondary">
                                                            <span class="text-theme-text-muted mr-2">{{ $i + 1 }}.</span>{{ $song['name'] }}
                                                            @if(!empty($song['cover']))
                                                                <span class="text-xs text-theme-text-muted">(cover)</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                    @if($remaining > 0)
                                                        <li class="text-xs text-theme-text-muted">+ {{ $remaining }} more song{{ $remaining !== 1 ? 's' : '' }}</li>
                                                    @endif
                                                </ol>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif

                            {{-- Status + Rating + Notes --}}
                            <div class="space-y-6">
                                {{-- Status --}}
                                <div>
                                    <label for="status" class="block text-sm font-medium text-theme-text-primary">Status</label>
                                    <select wire:model="status" id="status" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary sm:max-w-xs">
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Rating --}}
                                <div>
                                    <label class="block text-sm font-medium text-theme-text-primary">Rating</label>
                                    <div class="mt-2 flex items-center gap-1 flex-wrap">
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

                                {{-- Notes --}}
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-theme-text-primary">Personal Notes</label>
                                    <textarea wire:model="notes" id="notes" rows="3" placeholder="Your thoughts about the show..." class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"></textarea>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-theme-border-primary">
                                <button wire:click="back" type="button" class="text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Cancel</button>
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
