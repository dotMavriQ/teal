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
                            <a href="{{ route('games.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Games</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary line-clamp-1" aria-current="page">{{ $game->title }}</span>
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
                        @if($game->cover_url)
                            <img
                                src="{{ $game->cover_url }}"
                                alt="Cover of {{ $game->title }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                decoding="async"
                                width="400"
                                height="600"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        @endif
                    </div>

                    {{-- Platform Badges --}}
                    @if(count($game->platform ?? []) > 0)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($game->platform as $platform)
                                <span class="inline-flex items-center rounded-full bg-theme-accent-primary/10 px-3 py-1.5 text-sm font-medium text-theme-accent-primary ring-1 ring-inset ring-theme-accent-primary/20">
                                    {{ $platform }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Genre --}}
                    @if($game->genre)
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-theme-bg-tertiary px-3 py-1.5 text-sm font-medium text-theme-text-secondary ring-1 ring-inset ring-theme-border-primary">
                                {{ $game->genre }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Game Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">{{ $game->title }}</h1>

                    {{-- Status & Actions --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        {{-- Status Badge --}}
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                            @if($game->status->value === 'want_to_play') bg-theme-status-want-to-play-bg text-theme-status-want-to-play
                            @elseif($game->status->value === 'playing') bg-theme-status-playing-bg text-theme-status-playing
                            @elseif($game->status->value === 'played') bg-theme-status-played-bg text-theme-status-played
                            @else bg-theme-bg-tertiary text-theme-text-secondary
                            @endif
                        ">
                            {{ $game->status->label() }}
                        </span>

                        {{-- Ownership Badge --}}
                        @if($game->ownership)
                            <span class="inline-flex items-center rounded-full bg-theme-bg-tertiary px-3 py-1 text-sm font-medium text-theme-text-secondary ring-1 ring-inset ring-theme-border-primary">
                                {{ $game->ownership->label() }}
                            </span>
                        @endif

                        {{-- Rating (1-10 numbered buttons) --}}
                        <div class="flex items-center gap-1" role="group" aria-label="Rating">
                            @for($i = 1; $i <= 10; $i++)
                                <button
                                    wire:click="updateRating({{ $i }})"
                                    type="button"
                                    class="h-8 w-8 rounded-md text-sm font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-theme-accent-primary {{ $i <= ($game->rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                    aria-label="Rate {{ $i }} out of 10"
                                >
                                    {{ $i }}
                                </button>
                            @endfor
                        </div>

                        <div class="flex-1"></div>

                        {{-- Edit & Delete --}}
                        <a href="{{ route('games.edit', $game) }}" class="btn-secondary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover">
                            Edit
                        </a>
                        <button
                            wire:click="deleteGame"
                            wire:confirm="Are you sure you want to delete this game?"
                            type="button"
                            class="btn-danger inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Completion Progress Bar --}}
                    @if($game->completion_percentage !== null)
                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-theme-text-secondary">Completion</span>
                                <span class="text-sm font-medium text-theme-text-primary">{{ $game->completion_percentage }}%</span>
                            </div>
                            <div class="w-full bg-theme-bg-tertiary rounded-full h-2.5">
                                <div
                                    class="bg-theme-accent-primary h-2.5 rounded-full transition-all duration-300"
                                    style="width: {{ $game->completion_percentage }}%"
                                ></div>
                            </div>
                        </div>
                    @endif

                    {{-- Game Metadata --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($game->developer)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Developer</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->developer }}</dd>
                            </div>
                        @endif

                        @if($game->publisher)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Publisher</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->publisher }}</dd>
                            </div>
                        @endif

                        @if($game->release_date)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Release Date</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->release_date->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($game->rating)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Rating</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->rating }}/10</dd>
                            </div>
                        @endif

                        @if($game->hours_played)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Hours Played</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->hours_played }} hours</dd>
                            </div>
                        @endif

                        @if($game->date_started)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Date Started</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->date_started->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($game->date_finished)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Date Finished</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->date_finished->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($game->igdb_id)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">IGDB ID</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->igdb_id }}</dd>
                            </div>
                        @endif

                        @if($game->rawg_id)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">RAWG ID</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->rawg_id }}</dd>
                            </div>
                        @endif

                        @if($game->mobygames_id)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">MobyGames ID</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->mobygames_id }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                            <dd class="mt-1 text-sm text-theme-text-primary">{{ $game->created_at->format('F j, Y') }}</dd>
                        </div>
                    </dl>

                    {{-- Description --}}
                    @if($game->description)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Description</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($game->description)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if($game->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($game->notes)) !!}
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>
</div>
