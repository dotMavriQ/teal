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
                            <a href="{{ route('comics.index') }}" class="ml-4 text-sm font-medium text-theme-text-muted hover:text-theme-text-primary">Comics</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('comics.show', $comic) }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary line-clamp-1">{{ $comic->title }}</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary whitespace-nowrap" aria-current="page">Issue #{{ $issue->issue_number }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-x-8">
                {{-- Issue Cover --}}
                <div class="lg:col-span-1">
                    <div class="aspect-[2/3] overflow-hidden rounded-lg bg-theme-bg-tertiary flex items-center justify-center">
                        @if($issue->cover_url)
                            <img
                                src="{{ $issue->cover_url }}"
                                alt="Cover of {{ $issue->title ?: 'Issue #' . $issue->issue_number }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                width="400"
                                height="600"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        @endif
                    </div>

                    {{-- Comic Vine Link --}}
                    @if($issue->comicvine_url)
                        <div class="mt-4">
                            <a
                                href="{{ $issue->comicvine_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-md bg-theme-bg-tertiary px-3 py-2 text-sm font-medium text-theme-text-primary hover:bg-theme-bg-hover transition-colors w-full justify-center border border-theme-border-primary"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm-1 5v4h-4v2h4v4h2v-4h4v-2h-4v-4h-2z" />
                                </svg>
                                View Issue on Comic Vine
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Issue Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">
                        @if($issue->title)
                            {{ $issue->title }}
                        @else
                            Issue #{{ $issue->issue_number }}
                        @endif
                    </h1>
                    <p class="mt-2 text-xl text-theme-text-secondary">
                        <a href="{{ route('comics.show', $comic) }}" class="hover:text-theme-text-primary">{{ $comic->title }}</a>
                        @if($issue->issue_number)
                            <span class="text-theme-text-muted mx-1">&middot;</span>
                            <span class="text-theme-text-tertiary">#{{ $issue->issue_number }}</span>
                        @endif
                    </p>

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
                                    <option value="{{ $statusOption->value }}" @selected($issue->status === $statusOption)>
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
                                    <svg class="h-6 w-6 {{ $i <= ($issue->rating ?? 0) ? 'text-yellow-400' : 'text-theme-text-muted hover:text-yellow-200' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endfor
                        </div>
                    </div>

                    {{-- Issue Metadata --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($issue->cover_date)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Cover Date</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $issue->cover_date->format('F Y') }}</dd>
                            </div>
                        @endif

                        @if($issue->date_read)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Date Read</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $issue->date_read->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($issue->created_at)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $issue->created_at->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($issue->comicvine_issue_id)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Comic Vine Issue ID</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $issue->comicvine_issue_id }}</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Description --}}
                    @if($issue->description)
                        <div class="mt-8" x-data="{ expanded: false }">
                            <h2 class="text-lg font-medium text-theme-text-primary">Description</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary overflow-hidden transition-all duration-300 relative"
                                 :class="expanded ? 'max-h-full' : 'max-h-32'">
                                {!! nl2br(e($issue->description)) !!}

                                <div x-show="!expanded && '{{ strlen($issue->description) }}' > 300"
                                     class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-theme-bg-primary to-transparent pointer-events-none"></div>
                            </div>
                            @if(strlen($issue->description) > 300)
                                <button @click="expanded = !expanded" class="mt-2 text-sm font-medium text-theme-accent-primary hover:text-theme-accent-secondary">
                                    <span x-show="!expanded">Read More</span>
                                    <span x-show="expanded">Read Less</span>
                                </button>
                            @endif
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div class="mt-8">
                        <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                        <div class="mt-2">
                            <textarea
                                wire:blur="saveNotes($event.target.value)"
                                class="w-full rounded-md border-0 py-2 bg-theme-input-bg text-theme-input-text ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                rows="4"
                                placeholder="Add your personal notes about this issue..."
                            >{{ $issue->notes }}</textarea>
                            <p class="mt-1 text-xs text-theme-text-muted italic">Notes are saved automatically when you click away.</p>
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="mt-4 text-sm font-medium text-theme-success">
                            {{ session('message') }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>
</div>
