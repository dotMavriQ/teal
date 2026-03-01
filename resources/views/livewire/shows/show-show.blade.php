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
                            <a href="{{ route('shows.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Shows</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary line-clamp-1" aria-current="page">{{ $show->title }}</span>
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

            <div class="lg:grid lg:grid-cols-3 lg:gap-x-8">
                {{-- Poster --}}
                <div class="lg:col-span-1">
                    <div class="aspect-[2/3] overflow-hidden rounded-lg bg-theme-bg-tertiary flex items-center justify-center relative">
                        @if($show->poster_url)
                            <img
                                src="{{ $show->poster_url }}"
                                alt="Poster of {{ $show->title }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                decoding="async"
                                width="400"
                                height="600"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                            </svg>
                        @endif
                    </div>

                    {{-- Poster URL --}}
                    <div class="mt-3">
                        @if($showPosterForm)
                            <form wire:submit="savePosterUrl" class="flex gap-2">
                                <input
                                    wire:model="posterUrlInput"
                                    type="url"
                                    placeholder="https://..."
                                    class="flex-1 rounded-md border-0 py-1.5 px-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                                >
                                <button type="submit" class="rounded-md btn-primary px-3 py-1.5 text-sm font-medium shadow-sm">Save</button>
                                <button wire:click="togglePosterForm" type="button" class="rounded-md btn-secondary px-3 py-1.5 text-sm font-medium ring-1 ring-inset shadow-sm">Cancel</button>
                            </form>
                            @error('posterUrlInput')
                                <p class="mt-1 text-sm text-theme-danger">{{ $message }}</p>
                            @enderror
                        @else
                            <button wire:click="togglePosterForm" class="text-xs text-theme-text-muted hover:text-theme-accent-primary transition-colors">
                                {{ $show->poster_url ? 'Change poster URL' : 'Add poster URL' }}
                            </button>
                        @endif
                    </div>

                    {{-- Genre Tags --}}
                    @if(count($show->genre_list) > 0)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($show->genre_list as $genre)
                                <a
                                    href="{{ route('shows.index', ['genre' => $genre]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-theme-accent-primary/10 px-3 py-1.5 text-sm font-medium text-theme-accent-primary ring-1 ring-inset ring-theme-accent-primary/20 hover:bg-theme-accent-primary/20 transition-colors"
                                >
                                    {{ $genre }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Show Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">{{ $show->title }}</h1>

                    @if($show->original_title && $show->original_title !== $show->title)
                        <p class="mt-1 text-lg text-theme-text-tertiary italic">{{ $show->original_title }}</p>
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
                                    <option value="{{ $statusOption->value }}" @selected($show->status === $statusOption)>
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
                                    class="h-8 w-8 rounded-md text-sm font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-theme-accent-primary {{ $i <= ($show->rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                    aria-label="Rate {{ $i }} out of 10"
                                >
                                    {{ $i }}
                                </button>
                            @endfor
                        </div>

                        <div class="flex-1"></div>

                        {{-- IMDb Link --}}
                        @if($show->imdb_id)
                            <a
                                href="https://www.imdb.com/title/{{ $show->imdb_id }}/"
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
                        <a href="{{ route('shows.edit', $show) }}" class="btn-secondary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover">
                            Edit
                        </a>
                        <button
                            wire:click="deleteShow"
                            wire:confirm="Are you sure you want to delete this show?"
                            type="button"
                            class="btn-danger inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Show Metadata --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($show->year)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Year</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $show->year }}</dd>
                            </div>
                        @endif

                        @if($show->imdb_rating)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">IMDb Rating</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $show->imdb_rating }}/10</dd>
                            </div>
                        @endif

                        @if($show->release_date)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Release Date</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $show->release_date->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($show->date_added)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $show->date_added->format('F j, Y') }}</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Description --}}
                    @if($show->description)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Description</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($show->description)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Review --}}
                    @if($show->review)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Review</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($show->review)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if($show->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($show->notes)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
