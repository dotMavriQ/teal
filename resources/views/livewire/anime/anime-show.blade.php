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
                            <a href="{{ route('anime.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Anime</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary line-clamp-1" aria-current="page">{{ $anime->title }}</span>
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
                {{-- Poster --}}
                <div class="lg:col-span-1">
                    <div class="aspect-[2/3] overflow-hidden rounded-lg bg-theme-bg-tertiary flex items-center justify-center relative">
                        @if($anime->poster_url)
                            <img
                                src="{{ $anime->poster_url }}"
                                alt="Poster of {{ $anime->title }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                decoding="async"
                                width="400"
                                height="600"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                            </svg>
                        @endif

                        @if($anime->media_type)
                            <div class="absolute top-3 left-3 bg-theme-card-bg/95 rounded px-2.5 py-1 border border-theme-border-primary shadow-sm">
                                <span class="text-sm font-bold text-pink-400">{{ $anime->media_type }}</span>
                            </div>
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
                                {{ $anime->poster_url ? 'Change poster URL' : 'Add poster URL' }}
                            </button>
                        @endif
                    </div>

                    {{-- Genre Tags --}}
                    @if(count($anime->genre_list) > 0)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($anime->genre_list as $genre)
                                <a
                                    href="{{ route('anime.index', ['genre' => $genre]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-theme-accent-primary/10 px-3 py-1.5 text-sm font-medium text-theme-accent-primary ring-1 ring-inset ring-theme-accent-primary/20 hover:bg-theme-accent-primary/20 transition-colors"
                                >
                                    {{ $genre }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Studio Tags --}}
                    @if(count($anime->studio_list) > 0)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($anime->studio_list as $studio)
                                <span class="inline-flex items-center rounded-full bg-pink-400/10 px-3 py-1.5 text-sm font-medium text-pink-400 ring-1 ring-inset ring-pink-400/20">
                                    {{ $studio }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Anime Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">{{ $anime->title }}</h1>

                    @if($anime->original_title && $anime->original_title !== $anime->title)
                        <p class="mt-1 text-lg text-theme-text-tertiary italic">{{ $anime->original_title }}</p>
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
                                    <option value="{{ $statusOption->value }}" @selected($anime->status === $statusOption)>
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
                                    class="h-8 w-8 rounded-md text-sm font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-theme-accent-primary {{ $i <= ($anime->rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                    aria-label="Rate {{ $i }} out of 10"
                                >
                                    {{ $i }}
                                </button>
                            @endfor
                        </div>

                        <div class="flex-1"></div>

                        {{-- MAL Link --}}
                        @if($anime->mal_url)
                            <a
                                href="{{ $anime->mal_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-secondary inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                MAL
                            </a>
                        @endif

                        {{-- Fetch Metadata --}}
                        <button
                            wire:click="fetchMetadata"
                            wire:loading.attr="disabled"
                            wire:target="fetchMetadata"
                            type="button"
                            class="btn-secondary inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="fetchMetadata">Fetch Metadata</span>
                            <span wire:loading wire:target="fetchMetadata">Fetching...</span>
                        </button>

                        {{-- Edit & Delete --}}
                        <a href="{{ route('anime.edit', $anime) }}" class="btn-secondary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover">
                            Edit
                        </a>
                        <button
                            wire:click="deleteAnime"
                            wire:confirm="Are you sure you want to delete this anime?"
                            type="button"
                            class="btn-danger inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Metadata Preview --}}
                    @if($showMetadataPreview && $fetchedMetadata)
                        <div class="mt-6 rounded-lg bg-theme-card-bg ring-1 ring-theme-border-primary p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-theme-text-primary">Jikan Metadata Preview</h3>
                                <div class="flex gap-2">
                                    <button wire:click="applyMetadata" class="rounded-md btn-primary px-3 py-1.5 text-sm font-medium shadow-sm">
                                        Apply Missing Fields
                                    </button>
                                    <button wire:click="dismissMetadata" class="rounded-md btn-secondary px-3 py-1.5 text-sm font-medium ring-1 ring-inset shadow-sm">
                                        Dismiss
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-theme-text-muted mb-3">Only empty fields will be filled. Existing data is kept.</p>
                            <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2 text-sm">
                                @foreach(['description', 'poster_url', 'runtime_minutes', 'genres', 'studios', 'episodes_total', 'media_type', 'original_title', 'year'] as $field)
                                    @if(!empty($fetchedMetadata[$field]))
                                        @php $willApply = empty($anime->$field); @endphp
                                        <div class="flex gap-2 {{ $willApply ? '' : 'opacity-50' }}">
                                            <dt class="font-medium text-theme-text-secondary whitespace-nowrap">
                                                {{ str_replace('_', ' ', ucfirst($field)) }}:
                                            </dt>
                                            <dd class="text-theme-text-primary truncate">
                                                {{ Str::limit((string)$fetchedMetadata[$field], 80) }}
                                                @if(!$willApply)
                                                    <span class="text-xs text-theme-text-muted">(already set)</span>
                                                @else
                                                    <span class="text-xs text-theme-success">(will fill)</span>
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    {{-- Anime Metadata --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($anime->year)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Year</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $anime->year }}</dd>
                            </div>
                        @endif

                        @if($anime->episodes_total || $anime->episodes_watched)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Episodes</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">
                                    {{ $anime->episodes_watched ?? '?' }} / {{ $anime->episodes_total ?? '?' }}
                                </dd>
                            </div>
                        @endif

                        @if($anime->runtime_formatted)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Episode Length</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $anime->runtime_formatted }}</dd>
                            </div>
                        @endif

                        @if($anime->mal_score)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">MAL Score</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $anime->mal_score }}/10</dd>
                            </div>
                        @endif

                        @if($anime->date_started)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Date Started</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $anime->date_started->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($anime->date_finished)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Date Finished</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $anime->date_finished->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($anime->date_added)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $anime->date_added->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($anime->tags)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Tags</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $anime->tags }}</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Description --}}
                    @if($anime->description)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Description</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($anime->description)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Review --}}
                    @if($anime->review)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Review</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($anime->review)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if($anime->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($anime->notes)) !!}
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>
</div>
