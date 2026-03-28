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
                            <a href="{{ route('albums.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Collection</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary line-clamp-1" aria-current="page">{{ $album->title }}</span>
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
                {{-- Cover --}}
                <div class="lg:col-span-1">
                    <div class="aspect-square overflow-hidden rounded-lg bg-theme-bg-tertiary flex items-center justify-center relative">
                        @if($album->cover_url)
                            <img
                                src="{{ $album->cover_url }}"
                                alt="Cover for {{ $album->title }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                decoding="async"
                                width="500"
                                height="500"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="2" /><path d="M12 2a4.5 4.5 0 0 0 0 9" />
                            </svg>
                        @endif
                    </div>
                </div>

                {{-- Album Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">{{ $album->title }}</h1>
                    @if($album->artist)
                        <p class="mt-1 text-lg text-theme-text-secondary">{{ $album->artist }}</p>
                    @endif

                    {{-- Status, Rating & Actions --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        {{-- Status Badge --}}
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                            @switch($album->status->value)
                                @case('wishlist') bg-theme-status-want-bg text-theme-status-want @break
                                @case('listening') bg-theme-status-watching-bg text-theme-status-watching @break
                                @case('listened') bg-theme-status-read-bg text-theme-status-read @break
                                @case('shelved') bg-theme-bg-tertiary text-theme-text-secondary @break
                                @default bg-theme-bg-tertiary text-theme-text-secondary
                            @endswitch
                        ">
                            {{ $album->status->label() }}
                        </span>

                        {{-- Ownership Badge --}}
                        @if($album->ownership)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium bg-theme-bg-tertiary text-theme-text-secondary">
                                {{ $album->ownership->label() }}
                            </span>
                        @endif

                        {{-- Rating (1-5 stars) --}}
                        <div class="flex items-center gap-0.5" role="group" aria-label="Rating">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    wire:click="updateRating({{ $i }})"
                                    type="button"
                                    class="focus:outline-none focus:ring-2 focus:ring-theme-accent-primary rounded"
                                    aria-label="Rate {{ $i }} out of 5"
                                >
                                    <svg class="h-6 w-6 {{ $i <= ($album->rating ?? 0) ? 'text-theme-star-filled' : 'text-theme-star-empty hover:text-theme-star-filled/50' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        <div class="flex-1"></div>

                        {{-- Edit & Delete --}}
                        <a href="{{ route('albums.edit', $album) }}" class="btn-secondary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover">
                            Edit
                        </a>
                        <button
                            wire:click="deleteAlbum"
                            wire:confirm="Are you sure you want to delete this album?"
                            type="button"
                            class="btn-danger inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Metadata Grid --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($album->year)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Year</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $album->year }}</dd>
                            </div>
                        @endif

                        @if($album->format)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Format</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $album->format }}</dd>
                            </div>
                        @endif

                        @if(!empty($album->genre))
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Genre</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ implode(', ', $album->genre) }}</dd>
                            </div>
                        @endif

                        @if(!empty($album->styles))
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Styles</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ implode(', ', $album->styles) }}</dd>
                            </div>
                        @endif

                        @if($album->label)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Label</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $album->label }}</dd>
                            </div>
                        @endif

                        @if($album->country)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Country</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $album->country }}</dd>
                            </div>
                        @endif

                        @if($album->discogs_id || $album->discogs_master_id)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Discogs</dt>
                                <dd class="mt-1 text-sm">
                                    @if($album->discogs_master_id)
                                        <a href="https://www.discogs.com/master/{{ $album->discogs_master_id }}" target="_blank" rel="noopener noreferrer" class="text-theme-accent-primary hover:underline inline-flex items-center gap-1">
                                            View on Discogs
                                            <svg class="inline h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                        </a>
                                    @elseif($album->discogs_id)
                                        <a href="https://www.discogs.com/release/{{ $album->discogs_id }}" target="_blank" rel="noopener noreferrer" class="text-theme-accent-primary hover:underline inline-flex items-center gap-1">
                                            View on Discogs
                                            <svg class="inline h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                        </a>
                                    @endif
                                </dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                            <dd class="mt-1 text-sm text-theme-text-primary">{{ $album->created_at->format('F j, Y') }}</dd>
                        </div>
                    </dl>

                    {{-- Notes --}}
                    @if($album->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($album->notes)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Tracklist --}}
                    @if(!empty($album->tracklist))
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Tracklist</h2>
                            <p class="mt-1 text-sm text-theme-text-muted">{{ count($album->tracklist) }} track{{ count($album->tracklist) !== 1 ? 's' : '' }}</p>
                            <ol class="mt-3 space-y-1">
                                @foreach($album->tracklist as $index => $track)
                                    <li class="flex items-start gap-3 py-1.5 border-b border-theme-border-primary last:border-0">
                                        <span class="flex-shrink-0 w-8 text-right text-xs text-theme-text-muted mt-0.5">{{ $track['position'] ?? ($index + 1) }}</span>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-sm text-theme-text-primary">{{ $track['title'] ?? 'Unknown' }}</span>
                                        </div>
                                        @if(!empty($track['duration']))
                                            <span class="flex-shrink-0 text-xs text-theme-text-muted">{{ $track['duration'] }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
