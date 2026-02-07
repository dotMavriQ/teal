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
                            <a href="{{ route('movies.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Movies</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary line-clamp-1" aria-current="page">{{ $movie->title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-x-8">
                {{-- Poster --}}
                <div class="lg:col-span-1">
                    <div class="aspect-[2/3] overflow-hidden rounded-lg bg-theme-bg-tertiary flex items-center justify-center">
                        @if($movie->poster_url)
                            <img
                                src="{{ $movie->poster_url }}"
                                alt="Poster of {{ $movie->title }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                decoding="async"
                                width="400"
                                height="600"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M18 18.375v-5.25m0 5.25v-1.5c0-.621.504-1.125 1.125-1.125M18 13.125v1.5c0 .621.504 1.125 1.125 1.125M18 13.125c0-.621.504-1.125 1.125-1.125M6 13.125v1.5c0 .621-.504 1.125-1.125 1.125M6 13.125C6 12.504 5.496 12 4.875 12m-1.5 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M19.125 12h1.5m0 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h1.5m14.25 0h1.5" />
                            </svg>
                        @endif
                    </div>

                    {{-- Genre Tags --}}
                    @if(count($movie->genre_list) > 0)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($movie->genre_list as $genre)
                                <a
                                    href="{{ route('movies.index', ['genre' => $genre]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-theme-accent-primary/10 px-3 py-1.5 text-sm font-medium text-theme-accent-primary ring-1 ring-inset ring-theme-accent-primary/20 hover:bg-theme-accent-primary/20 transition-colors"
                                >
                                    {{ $genre }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Movie Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">{{ $movie->title }}</h1>

                    @if($movie->original_title && $movie->original_title !== $movie->title)
                        <p class="mt-1 text-lg text-theme-text-tertiary italic">{{ $movie->original_title }}</p>
                    @endif

                    @if($movie->director)
                        <p class="mt-2 text-xl text-theme-text-secondary">Directed by {{ $movie->director }}</p>
                    @endif

                    {{-- Status & Actions --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        {{-- Status Dropdown --}}
                        <div>
                            <label for="status" class="sr-only">Watching status</label>
                            <select
                                wire:change="updateStatus($event.target.value)"
                                id="status"
                                class="rounded-md border-0 py-2 pl-3 pr-10 bg-theme-input-bg text-theme-text-primary ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                            >
                                @foreach($statuses as $statusOption)
                                    <option value="{{ $statusOption->value }}" @selected($movie->status === $statusOption)>
                                        {{ $statusOption->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rating (1-10 numbered buttons) --}}
                        <div class="flex items-center gap-1" role="group" aria-label="Rating">
                            @for($i = 1; $i <= 10; $i++)
                                <button
                                    wire:click="updateRating({{ $i }})"
                                    type="button"
                                    class="h-8 w-8 rounded-md text-sm font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-theme-accent-primary {{ $i <= ($movie->rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                    aria-label="Rate {{ $i }} out of 10"
                                >
                                    {{ $i }}
                                </button>
                            @endfor
                        </div>

                        <div class="flex-1"></div>

                        {{-- IMDb Link --}}
                        @if($movie->imdb_id)
                            <a
                                href="https://www.imdb.com/title/{{ $movie->imdb_id }}/"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-secondary inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                IMDb
                            </a>
                        @endif

                        {{-- Edit & Delete --}}
                        <a href="{{ route('movies.edit', $movie) }}" class="btn-secondary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover">
                            Edit
                        </a>
                        <button
                            wire:click="deleteMovie"
                            wire:confirm="Are you sure you want to delete this movie?"
                            type="button"
                            class="btn-danger inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Movie Metadata --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($movie->year)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Year</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $movie->year }}</dd>
                            </div>
                        @endif

                        @if($movie->runtime_formatted)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Runtime</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $movie->runtime_formatted }}</dd>
                            </div>
                        @endif

                        @if($movie->imdb_rating)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">IMDb Rating</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $movie->imdb_rating }}/10{{ $movie->num_votes ? ' (' . number_format($movie->num_votes) . ' votes)' : '' }}</dd>
                            </div>
                        @endif

                        @if($movie->release_date)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Release Date</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $movie->release_date->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($movie->date_watched)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Date Watched</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $movie->date_watched->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($movie->date_added)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $movie->date_added->format('F j, Y') }}</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Description --}}
                    @if($movie->description)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Description</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($movie->description)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Review --}}
                    @if($movie->review)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Review</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($movie->review)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if($movie->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($movie->notes)) !!}
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>
</div>
