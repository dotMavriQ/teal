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
                            <a href="{{ route('books.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Books</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary line-clamp-1" aria-current="page">{{ $book->title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-x-8">
                {{-- Book Cover --}}
                <div class="lg:col-span-1">
                    <div class="aspect-[2/3] overflow-hidden rounded-lg bg-theme-bg-tertiary flex items-center justify-center">
                        @if($book->cover_url)
                            <img
                                src="{{ $book->cover_url }}"
                                alt="Cover of {{ $book->title }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                decoding="async"
                                width="400"
                                height="600"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        @endif
                    </div>

                    {{-- Tags --}}
                    @if(count($book->tags) > 0)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($book->tags as $tag)
                                <a
                                    href="{{ route('books.index', ['tag' => $tag]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-theme-accent-primary/10 px-3 py-1.5 text-sm font-medium text-theme-accent-primary ring-1 ring-inset ring-theme-accent-primary/20 hover:bg-theme-accent-primary/20 transition-colors"
                                >
                                    <svg class="h-4 w-4 text-theme-accent-primary" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.5 3A2.5 2.5 0 003 5.5v2.879a2.5 2.5 0 00.732 1.767l6.5 6.5a2.5 2.5 0 003.536 0l2.878-2.878a2.5 2.5 0 000-3.536l-6.5-6.5A2.5 2.5 0 008.38 3H5.5zM6 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Book Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">{{ $book->title }}</h1>

                    @if($book->author)
                        <p class="mt-2 text-xl text-theme-text-secondary">by {{ $book->author }}</p>
                    @endif

                    {{-- Reading Progress (for currently reading books) --}}
                    @if($book->status->value === 'reading' && $book->can_track_progress && $book->progress_percentage !== null)
                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex-1 h-2 bg-theme-bg-tertiary rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-400 to-emerald-400 rounded-full" style="width: {{ $book->progress_percentage }}%"></div>
                            </div>
                            <span class="text-sm text-theme-text-secondary whitespace-nowrap">{{ $book->current_page }}/{{ $book->page_count }}</span>
                        </div>
                    @endif

                    {{-- Status & Actions --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        {{-- Status Dropdown --}}
                        <div>
                            <label for="status" class="sr-only">Reading status</label>
                            <select
                                wire:change="updateStatus($event.target.value)"
                                id="status"
                                class="rounded-md border-0 py-2 pl-3 pr-10 bg-theme-input-bg text-theme-input-text ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                            >
                                @foreach($statuses as $statusOption)
                                    <option value="{{ $statusOption->value }}" @selected($book->status === $statusOption)>
                                        {{ $statusOption->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rating --}}
                        <div class="flex items-center gap-1" role="group" aria-label="Rating">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    wire:click="updateRating({{ $i }})"
                                    type="button"
                                    class="focus:outline-none focus:ring-2 focus:ring-theme-accent-primary rounded"
                                    aria-label="Rate {{ $i }} out of 5 stars"
                                >
                                    <svg class="h-6 w-6 {{ $i <= ($book->rating ?? 0) ? 'text-yellow-400' : 'text-theme-text-muted hover:text-yellow-200' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        {{-- Queue Button --}}
                        @if($book->status->value !== 'read')
                            @if($book->queue_position === null)
                                <button
                                    wire:click="addToQueue"
                                    type="button"
                                    class="btn-secondary inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add to Queue
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-theme-accent-primary/10 px-3 py-2 text-sm font-medium text-theme-accent-primary ring-1 ring-inset ring-theme-accent-primary/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    #{{ $book->queue_position }} in Queue
                                </span>
                                <button
                                    wire:click="removeFromQueue"
                                    type="button"
                                    class="p-2 rounded-md text-theme-text-muted hover:bg-red-50 hover:text-red-600"
                                    title="Remove from queue"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        @endif

                        <div class="flex-1"></div>

                        {{-- Edit & Delete --}}
                        <a href="{{ route('books.edit', $book) }}" class="btn-secondary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover">
                            Edit
                        </a>
                        <button
                            wire:click="deleteBook"
                            wire:confirm="Are you sure you want to delete this book?"
                            type="button"
                            class="btn-danger inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Book Metadata --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($book->publisher)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Publisher</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $book->publisher }}</dd>
                            </div>
                        @endif

                        @if($book->published_date)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Published</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $book->published_date->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($book->page_count)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Pages</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $book->page_count }}</dd>
                            </div>
                        @endif

                        @if($book->isbn13 || $book->isbn)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">ISBN</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $book->isbn13 ?? $book->isbn }}</dd>
                            </div>
                        @endif

                        @if($book->date_started)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Started</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $book->date_started->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($book->date_recorded ?? $book->date_added)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ ($book->date_recorded ?? $book->date_added)->format('F j, Y') }}</dd>
                            </div>
                        @endif

                    </dl>

                    {{-- Description --}}
                    @if($book->description)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Description</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($book->description)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if($book->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($book->notes)) !!}
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>
</div>
