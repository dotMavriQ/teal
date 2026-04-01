<div>
    <header class="bg-theme-bg-primary shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-theme-text-muted hover:text-theme-text-secondary">
                            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
                            </svg>
                            <span class="sr-only">Home</span>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('listening.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Listening</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('concerts.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Live</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary line-clamp-1" aria-current="page">{{ $concert->artist }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </header>

    <main class="py-10">
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

            <div class="lg:grid lg:grid-cols-3 lg:gap-x-8">
                {{-- Cover --}}
                <div class="lg:col-span-1">
                    <div class="aspect-[2/3] overflow-hidden rounded-lg bg-theme-bg-tertiary flex items-center justify-center relative">
                        @if($concert->cover_url)
                            <img
                                src="{{ $concert->cover_url }}"
                                alt="Cover for {{ $concert->artist }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                decoding="async"
                                width="400"
                                height="600"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                            </svg>
                        @endif
                    </div>
                </div>

                {{-- Concert Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">{{ $concert->artist }}</h1>
                    @if($concert->venue || $concert->city || $concert->country)
                        <p class="mt-1 text-lg text-theme-text-secondary">
                            {{ collect([$concert->venue, $concert->city, $concert->country])->filter()->implode(', ') }}
                        </p>
                    @endif

                    {{-- Status, Rating & Actions --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        {{-- Status Badge --}}
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                            @switch($concert->status->value)
                                @case('want_to_go') bg-theme-status-want-bg text-theme-status-want @break
                                @case('going') bg-theme-status-watching-bg text-theme-status-watching @break
                                @case('attended') bg-theme-status-read-bg text-theme-status-read @break
                                @case('missed') bg-theme-bg-tertiary text-theme-text-secondary @break
                                @default bg-theme-bg-tertiary text-theme-text-secondary
                            @endswitch
                        ">
                            {{ $concert->status->label() }}
                        </span>

                        {{-- Rating (1-10 numbered buttons) --}}
                        <div class="flex items-center gap-1" role="group" aria-label="Rating">
                            @for($i = 1; $i <= 10; $i++)
                                <button
                                    wire:click="updateRating({{ $i }})"
                                    type="button"
                                    class="h-8 w-8 rounded-md text-sm font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-theme-accent-primary {{ $i <= ($concert->rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                    aria-label="Rate {{ $i }} out of 10"
                                >
                                    {{ $i }}
                                </button>
                            @endfor
                        </div>

                        <div class="flex-1"></div>

                        {{-- Edit & Delete --}}
                        <a href="{{ route('concerts.edit', $concert) }}" class="btn-secondary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover">
                            Edit
                        </a>
                        <button
                            wire:click="deleteConcert"
                            wire:confirm="Are you sure you want to delete this concert?"
                            type="button"
                            class="btn-danger inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Metadata Grid --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($concert->event_date)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Event Date</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $concert->event_date->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($concert->tour_name)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Tour</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $concert->tour_name }}</dd>
                            </div>
                        @endif

                        @if($concert->venue)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Venue</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $concert->venue }}</dd>
                            </div>
                        @endif

                        @if($concert->city)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">City</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">
                                    {{ $concert->city }}{{ $concert->country ? ', ' . $concert->country : '' }}
                                </dd>
                            </div>
                        @endif

                        @if($concert->rating)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Your Rating</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $concert->rating }}/10</dd>
                            </div>
                        @endif

                        @if($concert->setlist_fm_id)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Setlist.fm</dt>
                                <dd class="mt-1 text-sm">
                                    <a href="https://www.setlist.fm/setlist/{{ $concert->setlist_fm_id }}" target="_blank" rel="noopener noreferrer" class="text-theme-accent-primary hover:underline inline-flex items-center gap-1">
                                        View on setlist.fm
                                        <svg class="inline h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                    </a>
                                </dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                            <dd class="mt-1 text-sm text-theme-text-primary">{{ $concert->created_at->format('F j, Y') }}</dd>
                        </div>
                    </dl>

                    {{-- Notes --}}
                    @if($concert->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($concert->notes)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Setlist --}}
                    @if(!empty($concert->setlist))
                        @php
                            $songs = is_string($concert->setlist) ? json_decode($concert->setlist, true) : $concert->setlist;
                            $mainSet = collect($songs)->where('encore', false)->where('tape', false)->values();
                            $encores = collect($songs)->where('encore', true)->values();
                            $tapes = collect($songs)->where('tape', true)->values();
                        @endphp
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Setlist</h2>
                            <p class="mt-1 text-sm text-theme-text-muted">{{ count($songs) }} song{{ count($songs) !== 1 ? 's' : '' }}</p>

                            {{-- Main set --}}
                            @if($mainSet->isNotEmpty())
                                <ol class="mt-3 space-y-1">
                                    @foreach($mainSet as $index => $song)
                                        <li class="flex items-start gap-3 py-1.5 border-b border-theme-border-primary last:border-0">
                                            <span class="flex-shrink-0 w-6 text-right text-xs text-theme-text-muted mt-0.5">{{ $index + 1 }}</span>
                                            <div class="flex-1 min-w-0">
                                                <span class="text-sm text-theme-text-primary">{{ $song['name'] }}</span>
                                                @if(!empty($song['cover']))
                                                    <span class="ml-2 text-xs text-theme-text-muted">(cover{{ !empty($song['with']) ? ' with ' . $song['with'] : '' }})</span>
                                                @elseif(!empty($song['with']))
                                                    <span class="ml-2 text-xs text-theme-text-muted">(with {{ $song['with'] }})</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            @endif

                            {{-- Tapes --}}
                            @if($tapes->isNotEmpty())
                                <div class="mt-4">
                                    <h3 class="text-sm font-medium text-theme-text-secondary mb-2">Tape</h3>
                                    <ol class="space-y-1">
                                        @foreach($tapes as $index => $song)
                                            <li class="flex items-start gap-3 py-1.5 border-b border-theme-border-primary last:border-0">
                                                <span class="flex-shrink-0 w-6 text-right text-xs text-theme-text-muted mt-0.5">{{ $index + 1 }}</span>
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-sm text-theme-text-secondary italic">{{ $song['name'] }}</span>
                                                    <span class="ml-2 inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium bg-theme-bg-tertiary text-theme-text-muted">tape</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif

                            {{-- Encores --}}
                            @if($encores->isNotEmpty())
                                <div class="mt-4">
                                    <h3 class="text-sm font-medium text-theme-text-secondary mb-2">Encore</h3>
                                    <ol class="space-y-1">
                                        @foreach($encores as $index => $song)
                                            <li class="flex items-start gap-3 py-1.5 border-b border-theme-border-primary last:border-0">
                                                <span class="flex-shrink-0 w-6 text-right text-xs text-theme-text-muted mt-0.5">{{ $index + 1 }}</span>
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-sm text-theme-text-primary">{{ $song['name'] }}</span>
                                                    <span class="ml-2 inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium bg-theme-status-read-bg text-theme-status-read">encore</span>
                                                    @if(!empty($song['cover']))
                                                        <span class="ml-1 text-xs text-theme-text-muted">(cover{{ !empty($song['with']) ? ' with ' . $song['with'] : '' }})</span>
                                                    @elseif(!empty($song['with']))
                                                        <span class="ml-1 text-xs text-theme-text-muted">(with {{ $song['with'] }})</span>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
