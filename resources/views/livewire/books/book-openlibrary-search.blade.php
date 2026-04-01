<div>
    {{-- Header --}}
    <header class="bg-theme-bg-primary shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
                    <li>
                        <a href="{{ route('reading.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Reading</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li>
                        <a href="{{ route('books.index') }}" class="text-theme-text-secondary hover:text-theme-text-primary">Books</a>
                    </li>
                    <li class="text-theme-text-muted">/</li>
                    <li><span class="text-theme-text-tertiary">Search Books</span></li>
                </ol>
            </nav>
            <h1 class="mt-1 text-2xl font-bold text-theme-text-primary">Search Books</h1>
        </div>
    </header>

    <main class="py-6">
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
                <div class="mb-4 rounded-md bg-red-900/20 p-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm font-medium text-red-400">{{ session('error') }}</p>
                </div>
            @endif

            {{-- ===== STEP: SEARCH ===== --}}
            @if($step === 'search')
                <div class="max-w-2xl mx-auto">
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg p-6">
                        <div class="text-center mb-6">
                            <svg class="mx-auto h-12 w-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            <h2 class="mt-2 text-lg font-semibold text-theme-text-primary">Search Books</h2>
                            <p class="mt-1 text-sm text-theme-text-secondary">Search for books to add to your library.</p>
                        </div>
                        <div class="mb-4 flex items-center justify-center gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="searchSource" value="google_books" class="text-theme-accent-primary focus:ring-theme-accent-primary">
                                <span class="text-sm text-theme-text-secondary">Google Books</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="searchSource" value="openlibrary" class="text-theme-accent-primary focus:ring-theme-accent-primary">
                                <span class="text-sm text-theme-text-secondary">OpenLibrary</span>
                            </label>
                        </div>
                        <form wire:submit="search" class="flex gap-3">
                            <input
                                wire:model="query"
                                type="text"
                                placeholder="Search by title, author, or ISBN..."
                                class="flex-1 rounded-md border-0 py-2 px-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                                autofocus
                            >
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                                Search
                            </button>
                        </form>
                    </div>
                </div>

            {{-- ===== STEP: RESULTS ===== --}}
            @elseif($step === 'results')
                <div class="mb-4 flex items-center justify-between">
                    <button wire:click="backToSearch" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        New Search
                    </button>
                    <form wire:submit="search" class="flex gap-2">
                        <input
                            wire:model="query"
                            type="text"
                            class="rounded-md border-0 py-1.5 px-3 text-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-theme-accent-primary"
                        >
                        <button type="submit" class="rounded-md btn-secondary px-3 py-1.5 text-sm font-medium ring-1 ring-inset shadow-sm">Search</button>
                    </form>
                </div>

                @if(empty($searchResults))
                    <div class="text-center py-12">
                        <p class="text-theme-text-secondary">No results found for "{{ $query }}".</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                        @foreach($searchResults as $index => $result)
                            <button
                                wire:click="selectResult({{ $index }})"
                                wire:loading.attr="disabled"
                                class="group relative bg-theme-card-bg rounded-lg shadow-sm ring-1 ring-theme-border-primary overflow-hidden hover:shadow-md hover:ring-theme-accent-primary transition-all text-left {{ $this->isResultDuplicate($result) ? 'opacity-50' : '' }}"
                            >
                                {{-- Duplicate badge --}}
                                @if($this->isResultDuplicate($result))
                                    <div class="absolute top-2 right-2 z-10">
                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase bg-theme-text-muted text-theme-text-inverted">
                                            In Library
                                        </span>
                                    </div>
                                @endif
                                <div class="aspect-[2/3] bg-theme-bg-tertiary flex items-center justify-center">
                                    @if($result['cover_url'])
                                        <img src="{{ $result['cover_url'] }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <svg class="h-10 w-10 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="p-2">
                                    <h3 class="text-xs font-medium text-theme-text-primary line-clamp-2 leading-tight">{{ $result['title'] }}</h3>
                                    @if($result['author'])
                                        <p class="mt-0.5 text-[10px] text-theme-text-secondary line-clamp-1">{{ $result['author'] }}</p>
                                    @endif
                                    @if($result['first_publish_year'])
                                        <p class="mt-0.5 text-[10px] text-theme-text-muted">{{ $result['first_publish_year'] }}</p>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($totalPages > 1)
                        <div class="mt-6 flex justify-center gap-2">
                            @if($currentPage > 1)
                                <button wire:click="loadPage({{ $currentPage - 1 }})" class="rounded-md btn-secondary px-3 py-1.5 text-sm ring-1 ring-inset shadow-sm">Previous</button>
                            @endif
                            <span class="px-3 py-1.5 text-sm text-theme-text-secondary">Page {{ $currentPage }} of {{ $totalPages }}</span>
                            @if($currentPage < $totalPages)
                                <button wire:click="loadPage({{ $currentPage + 1 }})" class="rounded-md btn-secondary px-3 py-1.5 text-sm ring-1 ring-inset shadow-sm">Next</button>
                            @endif
                        </div>
                    @endif
                @endif

                {{-- Loading overlay --}}
                <div wire:loading wire:target="selectResult" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
                    <div class="bg-theme-card-bg rounded-lg p-6 shadow-xl ring-1 ring-theme-border-primary flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-theme-accent-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-theme-text-primary">Fetching book details...</span>
                    </div>
                </div>

            {{-- ===== STEP: CONFIGURE ===== --}}
            @elseif($step === 'configure')
                <div class="mb-4">
                    <button wire:click="backToResults" class="inline-flex items-center gap-1 text-sm text-theme-text-secondary hover:text-theme-text-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back to Results
                    </button>
                </div>

                <div class="max-w-3xl mx-auto">
                    <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary rounded-lg overflow-hidden">
                        <div class="p-6">
                            {{-- Top: cover thumbnail + key info --}}
                            <div class="flex gap-4 mb-6">
                                <div class="w-24 flex-shrink-0">
                                    @if($cover_url)
                                        <img src="{{ $cover_url }}" alt="" class="w-full rounded-md object-cover aspect-[2/3]">
                                    @else
                                        <div class="w-full aspect-[2/3] rounded-md bg-theme-bg-tertiary flex items-center justify-center">
                                            <svg class="h-8 w-8 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="title" class="block text-sm font-medium text-theme-text-primary">Title</label>
                                    <input wire:model="title" type="text" id="title" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                    <div class="mt-3 grid grid-cols-3 gap-3">
                                        <div>
                                            <label for="author" class="block text-xs font-medium text-theme-text-muted">Author</label>
                                            <input wire:model="author" type="text" id="author" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                        <div>
                                            <label for="published_year" class="block text-xs font-medium text-theme-text-muted">Year</label>
                                            <input wire:model="published_year" type="number" id="published_year" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                        <div>
                                            <label for="page_count" class="block text-xs font-medium text-theme-text-muted">Pages</label>
                                            <input wire:model="page_count" type="number" id="page_count" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Publisher + ISBN --}}
                            <div class="mb-4 grid grid-cols-2 gap-4">
                                <div>
                                    <label for="publisher" class="block text-sm font-medium text-theme-text-primary">Publisher</label>
                                    <input wire:model="publisher" type="text" id="publisher" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                </div>
                                <div>
                                    <label for="isbn" class="block text-sm font-medium text-theme-text-primary">ISBN</label>
                                    <input wire:model="isbn" type="text" id="isbn" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-theme-text-primary">Description</label>
                                <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary"></textarea>
                            </div>

                            {{-- Status + Rating row --}}
                            <div class="mb-4 flex flex-wrap items-end gap-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-theme-text-primary">Status</label>
                                    <select wire:model="status" id="status" class="mt-1 block rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-theme-accent-primary">
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-theme-text-primary">Rating</label>
                                    <div class="mt-1 flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button
                                                wire:click="$set('rating', {{ $rating === $i ? 'null' : $i }})"
                                                type="button"
                                                class="h-8 w-8 rounded-md text-sm font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-theme-accent-primary {{ $i <= ($rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                            >{{ $i }}</button>
                                        @endfor
                                        <span class="ml-2 text-sm text-theme-text-muted">{{ $rating ? $rating . '/5' : 'Not rated' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-theme-border-primary">
                                <button wire:click="backToResults" type="button" class="text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Cancel</button>
                                <button wire:click="addBook" type="button" class="rounded-md btn-primary px-4 py-2 text-sm font-medium shadow-sm">
                                    Add to Library
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
