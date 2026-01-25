<div>
    <header class="bg-theme-bg-primary shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
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
                            <li><a href="{{ route('books.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Books</a></li>
                            <li class="text-theme-text-muted">/</li>
                            <li><span class="text-theme-text-tertiary">Read Queue</span></li>
                        </ol>
                    </nav>
                    <h1 class="mt-1 text-2xl font-bold text-theme-text-primary">Read Queue</h1>
                    <p class="mt-1 text-sm text-theme-text-secondary">Drag books to reorder your reading priorities</p>
                </div>
                <a href="{{ route('books.index') }}" class="btn-secondary inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium shadow-sm ring-1 ring-inset">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Library
                </a>
            </div>
        </div>
    </header>

    <main class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($books->isEmpty())
                <div class="text-center py-16 bg-theme-card-bg rounded-lg shadow-sm ring-1 ring-theme-border-primary">
                    <svg class="mx-auto h-12 w-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-theme-text-primary">Your read queue is empty</h3>
                    <p class="mt-1 text-sm text-theme-text-secondary">Add books from your "Want to Read" list to plan your reading.</p>
                    <div class="mt-6">
                        <a href="{{ route('books.index', ['status' => 'want_to_read']) }}" class="btn-primary inline-flex items-center rounded-md px-4 py-2 text-sm font-medium shadow-sm">
                            Browse Want to Read
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($books as $index => $book)
                        <div wire:key="queue-{{ $book->id }}" class="bg-theme-card-bg rounded-lg shadow-sm ring-1 ring-theme-border-primary p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                            {{-- Position & Controls --}}
                            <div class="flex flex-col items-center gap-1 text-theme-text-muted">
                                <span class="text-lg font-bold text-theme-text-secondary">{{ $index + 1 }}</span>
                                <div class="flex flex-col gap-0.5">
                                    <button
                                        wire:click="moveUp({{ $book->id }})"
                                        @if($index === 0) disabled @endif
                                        class="p-1 rounded hover:bg-theme-bg-hover disabled:opacity-30 disabled:cursor-not-allowed"
                                        title="Move up"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="moveDown({{ $book->id }})"
                                        @if($index === $books->count() - 1) disabled @endif
                                        class="p-1 rounded hover:bg-theme-bg-hover disabled:opacity-30 disabled:cursor-not-allowed"
                                        title="Move down"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Cover --}}
                            <a href="{{ route('books.show', $book) }}" class="flex-shrink-0">
                                <div class="w-16 h-24 bg-theme-bg-tertiary rounded overflow-hidden">
                                    @if($book->cover_url)
                                        <img src="{{ $book->cover_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center">
                                            <svg class="h-6 w-6 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </a>

                            {{-- Book Info --}}
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('books.show', $book) }}" class="block">
                                    <h3 class="text-sm font-medium text-theme-text-primary truncate hover:text-theme-accent-primary">{{ $book->title }}</h3>
                                </a>
                                @if($book->author)
                                    <p class="text-sm text-theme-text-secondary truncate">{{ $book->author }}</p>
                                @endif
                                <div class="mt-1 flex items-center gap-2">
                                    @if($book->page_count)
                                        <span class="text-xs text-theme-text-muted">{{ $book->page_count }} pages</span>
                                    @endif
                                    <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium
                                        @switch($book->status->value)
                                            @case('want_to_read') bg-theme-status-want-bg text-theme-status-want @break
                                            @case('reading') bg-theme-status-reading-bg text-theme-status-reading @break
                                        @endswitch
                                    ">{{ $book->status->label() }}</span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2">
                                {{-- Quick status change --}}
                                <select
                                    wire:change="updateStatus({{ $book->id }}, $event.target.value)"
                                    class="rounded-md border-0 py-1.5 pl-2 pr-7 text-xs ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"
                                >
                                    @foreach($statuses as $statusOption)
                                        @if($statusOption->value !== 'read')
                                            <option value="{{ $statusOption->value }}" @selected($book->status === $statusOption)>
                                                {{ $statusOption->label() }}
                                            </option>
                                        @endif
                                    @endforeach
                                    <option value="read">Mark as Read</option>
                                </select>

                                {{-- Move to top/bottom --}}
                                <div class="hidden sm:flex items-center gap-1">
                                    <button
                                        wire:click="moveToTop({{ $book->id }})"
                                        @if($index === 0) disabled @endif
                                        class="p-1.5 rounded text-theme-text-muted hover:bg-theme-bg-hover hover:text-theme-text-primary disabled:opacity-30 disabled:cursor-not-allowed"
                                        title="Move to top"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="moveToBottom({{ $book->id }})"
                                        @if($index === $books->count() - 1) disabled @endif
                                        class="p-1.5 rounded text-theme-text-muted hover:bg-theme-bg-hover hover:text-theme-text-primary disabled:opacity-30 disabled:cursor-not-allowed"
                                        title="Move to bottom"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Remove from queue --}}
                                <button
                                    wire:click="removeFromQueue({{ $book->id }})"
                                    class="p-1.5 rounded text-theme-text-muted hover:bg-red-50 hover:text-red-600"
                                    title="Remove from queue"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 text-center text-sm text-theme-text-muted">
                    {{ $books->count() }} book(s) in queue
                </div>
            @endif
        </div>
    </main>
</div>
