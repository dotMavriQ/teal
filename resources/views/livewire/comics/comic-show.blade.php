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
                            <a href="{{ route('reading.index') }}" class="ml-4 text-sm font-medium text-theme-text-muted hover:text-theme-text-primary">Reading</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('comics.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Comics</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary line-clamp-1" aria-current="page">{{ $comic->title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-x-8">
                {{-- Comic Cover --}}
                <div class="lg:col-span-1">
                    <div class="aspect-[2/3] overflow-hidden rounded-lg bg-theme-bg-tertiary flex items-center justify-center">
                        @if($comic->cover_url)
                            <img
                                src="{{ $comic->cover_url }}"
                                alt="Cover of {{ $comic->title }}"
                                class="h-full w-full object-cover transition-opacity duration-300"
                                loading="eager"
                                width="400"
                                height="600"
                            >
                        @else
                            <svg class="h-24 w-24 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        @endif
                    </div>

                    {{-- Comic Vine Link --}}
                    @if($comic->comicvine_url)
                        <div class="mt-4">
                            <a
                                href="{{ $comic->comicvine_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-md bg-theme-bg-tertiary px-3 py-2 text-sm font-medium text-theme-text-primary hover:bg-theme-bg-hover transition-colors w-full justify-center"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm-1 5v4h-4v2h4v4h2v-4h4v-2h-4v-4h-2z" />
                                </svg>
                                View on Comic Vine
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Comic Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">{{ $comic->title }}</h1>

                    @if($comic->publisher)
                        <p class="mt-2 text-xl text-theme-text-secondary">{{ $comic->publisher }} @if($comic->start_year)({{ $comic->start_year }})@endif</p>
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
                                    <option value="{{ $statusOption->value }}" @selected($comic->status === $statusOption)>
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
                                    <svg class="h-6 w-6 {{ $i <= ($comic->rating ?? 0) ? 'text-yellow-400' : 'text-theme-text-muted hover:text-yellow-200' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        <div class="flex-1"></div>

                        {{-- Edit & Delete --}}
                        <a href="{{ route('comics.edit', $comic) }}" class="btn-secondary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover">
                            Edit
                        </a>
                        <button
                            wire:click="deleteComic"
                            wire:confirm="Are you sure you want to delete this comic?"
                            type="button"
                            class="btn-danger inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Comic Metadata --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($comic->issue_count)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Issue Count</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $comic->issue_count }}</dd>
                            </div>
                        @endif

                        @if($comic->start_year)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Started In</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $comic->start_year }}</dd>
                            </div>
                        @endif

                        @if($comic->date_started)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">You Started On</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $comic->date_started->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($comic->date_finished)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">You Finished On</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $comic->date_finished->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($comic->date_added)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Added to Library</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $comic->date_added->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($comic->comicvine_volume_id)
                            <div>
                                <dt class="text-sm font-medium text-theme-text-secondary">Comic Vine ID</dt>
                                <dd class="mt-1 text-sm text-theme-text-primary">{{ $comic->comicvine_volume_id }}</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Creators --}}
                    @if($comic->creators)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Creators</h2>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($comic->creatorsArray as $creator)
                                    <span class="inline-flex items-center rounded bg-theme-bg-tertiary px-2.5 py-0.5 text-xs font-medium text-theme-text-primary">
                                        {{ $creator }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Characters --}}
                    @if($comic->characters)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">Characters</h2>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($comic->charactersArray as $character)
                                    <span class="inline-flex items-center rounded bg-theme-bg-tertiary px-2.5 py-0.5 text-xs font-medium text-theme-text-secondary border border-theme-border-primary">
                                        {{ $character }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Description --}}
                    @if($comic->description)
                        <div class="mt-8" x-data="{ expanded: false }">
                            <h2 class="text-lg font-medium text-theme-text-primary">Description</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary overflow-hidden transition-all duration-300 relative"
                                 :class="expanded ? 'max-h-full' : 'max-h-32'">
                                {!! nl2br(e($comic->description)) !!}

                                <div x-show="!expanded && '{{ strlen($comic->description) }}' > 300"
                                     class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-theme-bg-primary to-transparent pointer-events-none"></div>
                            </div>
                            @if(strlen($comic->description) > 300)
                                <button @click="expanded = !expanded" class="mt-2 text-sm font-medium text-theme-accent-primary hover:text-theme-accent-secondary">
                                    <span x-show="!expanded">Read More</span>
                                    <span x-show="expanded">Read Less</span>
                                </button>
                            @endif
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if($comic->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-theme-text-primary">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-theme-text-secondary">
                                {!! nl2br(e($comic->notes)) !!}
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- ===== ISSUES SECTION ===== --}}
            <div class="mt-12">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-bold text-theme-text-primary">Issues</h2>
                        @if($issueStats['total'] > 0)
                            <div class="flex items-center gap-2 text-sm text-theme-text-secondary">
                                <span>{{ $issueStats['read'] }}/{{ $issueStats['total'] }} read</span>
                                @if($issueStats['reading'] > 0)
                                    <span class="text-theme-text-muted">&middot;</span>
                                    <span>{{ $issueStats['reading'] }} reading</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if($comic->comicvine_volume_id)
                        <button
                            wire:click="fetchIssues"
                            wire:loading.attr="disabled"
                            wire:target="fetchIssues"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-md btn-secondary px-3 py-2 text-sm font-medium shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover"
                        >
                            <svg wire:loading.remove wire:target="fetchIssues" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <svg wire:loading wire:target="fetchIssues" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="fetchIssues">Fetch Issues from Comic Vine</span>
                            <span wire:loading wire:target="fetchIssues">Fetching...</span>
                        </button>
                    @endif
                </div>

                {{-- Flash messages --}}
                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-theme-success-bg p-3 flex items-center gap-2">
                        <svg class="h-5 w-5 text-theme-success" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium text-theme-success-text">{{ session('message') }}</p>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="mb-4 rounded-md bg-red-900/20 p-3 flex items-center gap-2">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium text-red-400">{{ session('error') }}</p>
                    </div>
                @endif

                @if($issues->isEmpty())
                    <div class="text-center py-8 bg-theme-card-bg rounded-lg ring-1 ring-theme-border-primary">
                        <svg class="mx-auto h-10 w-10 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <p class="mt-2 text-sm text-theme-text-secondary">No issues yet.</p>
                        @if($comic->comicvine_volume_id)
                            <p class="mt-1 text-xs text-theme-text-muted">Click "Fetch Issues from Comic Vine" to populate.</p>
                        @endif
                    </div>
                @else
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-theme-border-primary">
                                <thead class="bg-theme-bg-secondary">
                                    <tr>
                                        <th scope="col" class="py-3 pl-4 pr-2 text-left text-xs font-medium text-theme-text-secondary uppercase tracking-wider w-16">#</th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-theme-text-secondary uppercase tracking-wider">Title</th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-theme-text-secondary uppercase tracking-wider w-28">Cover Date</th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-theme-text-secondary uppercase tracking-wider w-36">Status</th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-theme-text-secondary uppercase tracking-wider w-32">Rating</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-theme-border-primary">
                                    @foreach($issues as $issue)
                                        <tr class="hover:bg-theme-bg-hover transition-colors" wire:key="issue-{{ $issue->id }}">
                                            {{-- Issue Number --}}
                                            <td class="py-2 pl-4 pr-2 text-sm text-theme-text-primary font-medium whitespace-nowrap">
                                                {{ $issue->issue_number ?? '—' }}
                                            </td>

                                            {{-- Title --}}
                                            <td class="px-2 py-2 text-sm text-theme-text-primary">
                                                <a href="{{ route('comics.issues.show', [$comic, $issue]) }}" class="flex items-center gap-2 hover:text-theme-accent-primary transition-colors group">
                                                    @if($issue->cover_url)
                                                        <img src="{{ $issue->cover_url }}" alt="" class="h-8 w-6 object-cover rounded flex-shrink-0 group-hover:opacity-75 transition-opacity" loading="lazy">
                                                    @endif
                                                    <span class="line-clamp-1 font-medium">{{ $issue->title ?: 'Issue #' . ($issue->issue_number ?? '?') }}</span>
                                                </a>
                                            </td>

                                            {{-- Cover Date --}}
                                            <td class="px-2 py-2 text-sm text-theme-text-secondary whitespace-nowrap">
                                                {{ $issue->cover_date?->format('M Y') ?? '—' }}
                                            </td>

                                            {{-- Status --}}
                                            <td class="px-2 py-2">
                                                <select
                                                    wire:change="updateIssueStatus({{ $issue->id }}, $event.target.value)"
                                                    class="rounded border-0 py-1 pl-2 pr-7 text-xs bg-theme-input-bg text-theme-input-text ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"
                                                >
                                                    @foreach($statuses as $statusOption)
                                                        <option value="{{ $statusOption->value }}" @selected($issue->status === $statusOption)>
                                                            {{ $statusOption->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            {{-- Rating --}}
                                            <td class="px-2 py-2">
                                                <div class="flex items-center gap-0.5">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <button
                                                            wire:click="updateIssueRating({{ $issue->id }}, {{ $i }})"
                                                            type="button"
                                                            class="focus:outline-none"
                                                            aria-label="Rate issue {{ $i }} out of 5"
                                                        >
                                                            <svg class="h-4 w-4 {{ $i <= ($issue->rating ?? 0) ? 'text-yellow-400' : 'text-theme-text-muted hover:text-yellow-200' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    @endfor
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>

    {{-- Selective Import Modal --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-950 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showImportModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-theme-bg-primary rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-theme-border-primary">
                    <div class="bg-theme-bg-primary px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-theme-text-primary" id="modal-title">
                                    Select Issues to Import
                                </h3>
                                <div class="mt-4 max-h-96 overflow-y-auto pr-2">
                                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-theme-border-primary">
                                        <div class="flex items-center">
                                            <input
                                                id="select-all"
                                                type="checkbox"
                                                wire:model.live="selectAll"
                                                class="h-4 w-4 rounded border-theme-border-primary text-theme-accent-primary focus:ring-theme-accent-primary bg-theme-input-bg"
                                            >
                                            <label for="select-all" class="ml-2 block text-sm text-theme-text-primary font-bold">
                                                Select All ({{ count($availableIssues) }})
                                            </label>
                                        </div>
                                        <div class="text-xs text-theme-text-muted italic">
                                            Only showing issues not already in library
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-2">
                                        @foreach($availableIssues as $issue)
                                            <div class="flex items-center p-2 rounded hover:bg-theme-bg-hover transition-colors">
                                                <input
                                                    id="issue-{{ $issue['issue_id'] }}"
                                                    type="checkbox"
                                                    value="{{ $issue['issue_id'] }}"
                                                    wire:model="selectedIssueIds"
                                                    class="h-4 w-4 rounded border-theme-border-primary text-theme-accent-primary focus:ring-theme-accent-primary bg-theme-input-bg"
                                                >
                                                <label for="issue-{{ $issue['issue_id'] }}" class="ml-3 flex items-center gap-3 cursor-pointer flex-1">
                                                    @if($issue['cover_url'])
                                                        <img src="{{ $issue['cover_url'] }}" alt="" class="h-10 w-8 object-cover rounded shadow-sm">
                                                    @endif
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-medium text-theme-text-primary">
                                                            #{{ $issue['issue_number'] }} {{ $issue['title'] ?: 'Issue #' . $issue['issue_number'] }}
                                                        </span>
                                                        @if($issue['cover_date'])
                                                            <span class="text-xs text-theme-text-muted">{{ \Carbon\Carbon::parse($issue['cover_date'])->format('M Y') }}</span>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-theme-bg-secondary px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button
                            wire:click="importSelectedIssues"
                            wire:loading.attr="disabled"
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-theme-accent-primary text-base font-medium text-white hover:bg-theme-accent-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-theme-accent-primary sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            <span wire:loading.remove wire:target="importSelectedIssues">Import {{ count($selectedIssueIds) }} Issues</span>
                            <span wire:loading wire:target="importSelectedIssues">Importing...</span>
                        </button>
                        <button
                            @click="$wire.set('showImportModal', false)"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-theme-border-primary shadow-sm px-4 py-2 bg-theme-bg-tertiary text-base font-medium text-theme-text-primary hover:bg-theme-bg-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-theme-accent-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
