<div>
    {{-- Header --}}
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol role="list" class="flex items-center space-x-2 text-sm">
                            <li>
                                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </li>
                            <li class="text-gray-300">/</li>
                            <li><span class="text-gray-500">Books</span></li>
                        </ol>
                    </nav>
                    <h1 class="mt-1 text-2xl font-bold text-gray-900">My Library</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('books.import') }}" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span class="hidden sm:inline">Import</span>
                    </a>
                    <a href="{{ route('books.create') }}" class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">Add Book</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Flash Message --}}
            @if (session()->has('message'))
                <div class="mb-4 rounded-md bg-green-50 p-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                </div>
            @endif

            {{-- Toolbar --}}
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-900/5 p-4 mb-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    {{-- Left: Search & Filters --}}
                    <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                        {{-- Search --}}
                        <div class="relative flex-1 max-w-sm">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                            <input
                                wire:model.live.debounce.300ms="search"
                                type="search"
                                placeholder="Search..."
                                class="block w-full rounded-md border-0 py-1.5 pl-9 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-blue-600"
                            >
                        </div>

                        {{-- Status Filter --}}
                        <select
                            wire:model.live="status"
                            class="rounded-md border-0 py-1.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600"
                        >
                            <option value="">All statuses</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                            @endforeach
                        </select>

                        {{-- Sort --}}
                        <select
                            wire:model.live="sortBy"
                            class="rounded-md border-0 py-1.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600"
                        >
                            <option value="title">Title</option>
                            <option value="author">Author</option>
                            <option value="rating">Rating</option>
                            <option value="date_finished">Date Read</option>
                            <option value="created_at">Date Added</option>
                            <option value="updated_at">Recently Updated</option>
                        </select>

                        {{-- Sort Direction --}}
                        <button
                            wire:click="$set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')"
                            class="inline-flex items-center justify-center rounded-md p-1.5 text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            title="{{ $sortDirection === 'asc' ? 'Ascending' : 'Descending' }}"
                        >
                            @if($sortDirection === 'asc')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                </svg>
                            @else
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                                </svg>
                            @endif
                        </button>
                    </div>

                    {{-- Right: View Toggle & Actions --}}
                    <div class="flex items-center gap-2">
                        {{-- Bulk Actions --}}
                        @if(count($selected) > 0)
                            <span class="text-sm text-gray-500">{{ count($selected) }} selected</span>
                            <button
                                wire:click="deleteSelected"
                                wire:confirm="Delete {{ count($selected) }} book(s)?"
                                class="inline-flex items-center gap-1 rounded-md bg-red-600 px-2.5 py-1.5 text-sm font-medium text-white hover:bg-red-500"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        @endif

                        <div class="h-6 w-px bg-gray-200"></div>

                        {{-- View Toggle --}}
                        <div class="inline-flex rounded-md shadow-sm">
                            <button
                                wire:click="setViewMode('gallery')"
                                class="inline-flex items-center px-2.5 py-1.5 text-sm font-medium rounded-l-md border {{ $viewMode === 'gallery' ? 'bg-gray-100 text-gray-900 border-gray-300' : 'bg-white text-gray-500 border-gray-300 hover:bg-gray-50' }}"
                                title="Gallery"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </button>
                            <button
                                wire:click="setViewMode('list')"
                                class="inline-flex items-center px-2.5 py-1.5 text-sm font-medium rounded-r-md border-t border-r border-b -ml-px {{ $viewMode === 'list' ? 'bg-gray-100 text-gray-900 border-gray-300' : 'bg-white text-gray-500 border-gray-300 hover:bg-gray-50' }}"
                                title="List"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                            </button>
                        </div>

                        <a href="{{ route('books.settings') }}" class="inline-flex items-center rounded-md p-1.5 text-gray-500 hover:bg-gray-100" title="Settings">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Select All (when books exist) --}}
                @if($books->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
                        <input
                            wire:model.live="selectAll"
                            type="checkbox"
                            id="selectAll"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                        >
                        <label for="selectAll" class="text-sm text-gray-600">Select all ({{ $books->total() }} books)</label>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            @if($books->isEmpty())
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No books yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a book or importing your library.</p>
                    <div class="mt-6 flex justify-center gap-3">
                        <a href="{{ route('books.import') }}" class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Import Books
                        </a>
                        <a href="{{ route('books.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-500">
                            Add Book
                        </a>
                    </div>
                </div>
            @else
                <div wire:loading.class="opacity-50" wire:target="gotoPage, previousPage, nextPage, search, status, sortBy, sortDirection, setViewMode">
                    @if($viewMode === 'gallery')
                        {{-- Gallery View --}}
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                            @foreach($books as $book)
                                <article wire:key="book-{{ $book->id }}" class="group relative bg-white rounded-lg shadow-sm ring-1 ring-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                                    <div class="absolute top-2 left-2 z-10">
                                        <input wire:model.live="selected" type="checkbox" value="{{ $book->id }}" class="h-4 w-4 rounded border-gray-300 text-blue-600 bg-white/90 shadow-sm">
                                    </div>
                                    <a href="{{ route('books.show', $book) }}" class="block">
                                        <div class="aspect-[2/3] bg-gray-100 flex items-center justify-center">
                                            @if($book->cover_url)
                                                <img src="{{ $book->cover_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                            @else
                                                <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="p-2">
                                            <h3 class="text-xs font-medium text-gray-900 line-clamp-2 leading-tight">{{ $book->title }}</h3>
                                            @if($book->author)
                                                <p class="mt-0.5 text-xs text-gray-500 truncate">{{ $book->author }}</p>
                                            @endif
                                            <div class="mt-1.5 flex items-center justify-between">
                                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium
                                                    @switch($book->status->value)
                                                        @case('want_to_read') bg-blue-50 text-blue-700 @break
                                                        @case('reading') bg-amber-50 text-amber-700 @break
                                                        @case('read') bg-green-50 text-green-700 @break
                                                    @endswitch
                                                ">{{ $book->status->label() }}</span>
                                                @if($book->rating)
                                                    <span class="text-[10px] text-gray-500">{{ $book->rating }}/5</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @else
                        {{-- List View (Table) --}}
                        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="w-10 px-3 py-3"></th>
                                            <th scope="col" class="w-12 px-2 py-3"></th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title / Author</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Status</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Rating</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Pages</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">ISBN</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Date Read</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Shelves</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($books as $book)
                                            <tr wire:key="book-{{ $book->id }}" class="hover:bg-gray-50">
                                                <td class="px-3 py-2">
                                                    <input wire:model.live="selected" type="checkbox" value="{{ $book->id }}" class="h-4 w-4 rounded border-gray-300 text-blue-600">
                                                </td>
                                                <td class="px-2 py-2">
                                                    <div class="w-8 h-12 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                                        @if($book->cover_url)
                                                            <img src="{{ $book->getThumbnailUrl(50) }}" alt="" class="h-full w-full object-cover">
                                                        @else
                                                            <div class="h-full w-full flex items-center justify-center">
                                                                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <a href="{{ route('books.show', $book) }}" class="block">
                                                        <div class="text-sm font-medium text-gray-900 hover:text-blue-600">{{ Str::limit($book->title, 50) }}</div>
                                                        <div class="text-xs text-gray-500">{{ $book->author ?? '—' }}</div>
                                                    </a>
                                                </td>
                                                <td class="px-3 py-2 hidden sm:table-cell">
                                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium
                                                        @switch($book->status->value)
                                                            @case('want_to_read') bg-blue-50 text-blue-700 @break
                                                            @case('reading') bg-amber-50 text-amber-700 @break
                                                            @case('read') bg-green-50 text-green-700 @break
                                                        @endswitch
                                                    ">{{ $book->status->label() }}</span>
                                                </td>
                                                <td class="px-3 py-2 hidden md:table-cell">
                                                    @if($book->rating)
                                                        <div class="flex items-center gap-0.5">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <svg class="h-3.5 w-3.5 {{ $i <= $book->rating ? 'text-amber-400' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                                                </svg>
                                                            @endfor
                                                        </div>
                                                    @else
                                                        <span class="text-xs text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-xs text-gray-500 hidden lg:table-cell">
                                                    {{ $book->page_count ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-gray-500 font-mono hidden lg:table-cell">
                                                    {{ $book->isbn13 ?? $book->isbn ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-gray-500 hidden xl:table-cell">
                                                    {{ $book->date_finished?->format('M j, Y') ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2 hidden xl:table-cell">
                                                    @if($book->bookShelves->isNotEmpty())
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach($book->bookShelves->take(2) as $shelf)
                                                                <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-600">{{ $shelf->name }}</span>
                                                            @endforeach
                                                            @if($book->bookShelves->count() > 2)
                                                                <span class="text-[10px] text-gray-400">+{{ $book->bookShelves->count() - 2 }}</span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-xs text-gray-400">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $books->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
