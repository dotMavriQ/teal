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
                            <span class="ml-4 text-sm font-medium text-theme-text-secondary">Playing</span>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('board-games.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Board Games</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary line-clamp-1" aria-current="page">{{ $boardGame->title }}</span>
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
                {{-- Cover + Genres --}}
                <div class="lg:col-span-1">
                    <div class="aspect-[2/3] overflow-hidden rounded-lg bg-theme-bg-tertiary flex items-center justify-center relative">
                        @if($boardGame->cover_url)
                            <img
                                src="{{ $boardGame->cover_url }}"
                                alt="Cover of {{ $boardGame->title }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                decoding="async"
                                width="400"
                                height="600"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        @endif
                    </div>

                    {{-- Genre Badges --}}
                    @if(count($boardGame->genre ?? []) > 0)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($boardGame->genre as $genre)
                                <a href="{{ route('board-games.index', ['genre' => $genre]) }}" class="inline-flex items-center rounded-full bg-theme-bg-tertiary px-3 py-1.5 text-sm font-medium text-theme-text-secondary ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover hover:text-theme-text-primary transition-colors">
                                    {{ $genre }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Board Game Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">{{ $boardGame->title }}</h1>
                    @if($boardGame->designer)
                        <p class="mt-1 text-lg text-theme-text-secondary">{{ $boardGame->designer }}</p>
                    @endif

                    {{-- Status, Rating & Actions --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        {{-- Status Badge --}}
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                            bg-theme-status-{{ $boardGame->status->color() }}-bg text-theme-status-{{ $boardGame->status->color() }}
                        ">
                            {{ $boardGame->status->label() }}
                        </span>

                        {{-- Rating (1-10 numbered buttons) --}}
                        <div class="flex items-center gap-1" role="group" aria-label="Rating">
                            @for($i = 1; $i <= 10; $i++)
                                <button
                                    wire:click="updateRating({{ $i }})"
                                    type="button"
                                    class="h-8 w-8 rounded-md text-sm font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-theme-accent-primary {{ $i <= ($boardGame->rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                    aria-label="Rate {{ $i }} out of 10"
                                >
                                    {{ $i }}
                                </button>
                            @endfor
                        </div>

                        <div class="flex-1"></div>

                        {{-- Edit & Delete --}}
                        <a href="{{ route('board-games.edit', $boardGame) }}" class="btn-secondary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover">
                            Edit
                        </a>
                        <button
                            wire:click="deleteBoardGame"
                            wire:confirm="Are you sure you want to delete this board game?"
                            type="button"
                            class="btn-danger inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Metadata Grid --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($boardGame->year_published)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Year Published</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $boardGame->year_published }}</dd>
                            </div>
                        @endif

                        @if($boardGame->designer)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Designer</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $boardGame->designer }}</dd>
                            </div>
                        @endif

                        @if($boardGame->publisher)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Publisher</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $boardGame->publisher }}</dd>
                            </div>
                        @endif

                        @if($boardGame->min_players || $boardGame->max_players)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Players</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">
                                    @if($boardGame->min_players === $boardGame->max_players)
                                        {{ $boardGame->min_players }}
                                    @else
                                        {{ $boardGame->min_players }}–{{ $boardGame->max_players }}
                                    @endif
                                </dd>
                            </div>
                        @endif

                        @if($boardGame->playing_time)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Playing Time</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $boardGame->playing_time }} min</dd>
                            </div>
                        @endif

                        @if($boardGame->plays !== null)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Plays</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $boardGame->plays }}</dd>
                            </div>
                        @endif

                        @if($boardGame->rating)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Your Rating</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $boardGame->rating }}/10</dd>
                            </div>
                        @endif

                        @if($boardGame->bgg_rating)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">BGG Rating</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $boardGame->bgg_rating }}/10</dd>
                            </div>
                        @endif

                        @if($boardGame->bgg_id)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">BoardGameGeek</dt>
                                <dd class="mt-1 text-sm">
                                    <a href="https://boardgamegeek.com/boardgame/{{ $boardGame->bgg_id }}" target="_blank" rel="noopener noreferrer" class="text-theme-accent-primary hover:underline inline-flex items-center gap-1">
                                        {{ $boardGame->bgg_id }}
                                        <svg class="inline h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                    </a>
                                </dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                            <dd class="mt-1 text-sm text-theme-text-primary">{{ $boardGame->created_at->format('F j, Y') }}</dd>
                        </div>
                    </dl>

                    {{-- Description --}}
                    @if($boardGame->description)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Description</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($boardGame->description)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if($boardGame->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($boardGame->notes)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
