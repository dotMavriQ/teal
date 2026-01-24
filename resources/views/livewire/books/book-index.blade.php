<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
                            </svg>
                            <span class="sr-only">Home</span>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('reading.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Reading</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500" aria-current="page">Books</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="mt-2 flex items-center justify-between">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    Books
                </h1>
                <div class="flex gap-3">
                    <a href="{{ route('books.import') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M9.25 13.25a.75.75 0 001.5 0V4.636l2.955 3.129a.75.75 0 001.09-1.03l-4.25-4.5a.75.75 0 00-1.09 0l-4.25 4.5a.75.75 0 101.09 1.03L9.25 4.636v8.614z" />
                            <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" />
                        </svg>
                        Import
                    </a>
                    <a href="{{ route('books.create') }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        Add Book
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Flash Message --}}
            @if (session()->has('message'))
                <div class="mb-4 rounded-md bg-green-50 p-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Filters --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 gap-4">
                    {{-- Search --}}
                    <div class="relative flex-1 max-w-md">
                        <label for="search" class="sr-only">Search books</label>
                        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="search"
                            id="search"
                            class="block w-full rounded-md border-0 py-2 pl-9 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm"
                            placeholder="Search by title or author..."
                        >
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label for="status" class="sr-only">Filter by status</label>
                        <select
                            wire:model.live="status"
                            id="status"
                            class="block rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6"
                        >
                            <option value="">All statuses</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Bulk Actions --}}
                <div class="flex items-center gap-3">
                    @if(count($selected) > 0)
                        <span class="text-sm text-gray-600">{{ count($selected) }} selected</span>
                        <button
                            wire:click="deleteSelected"
                            wire:confirm="Are you sure you want to delete {{ count($selected) }} book(s)?"
                            type="button"
                            class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Selected
                        </button>
                    @endif
                    <a
                        href="{{ route('books.settings') }}"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        title="Settings"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Select All --}}
            @if($books->isNotEmpty())
                <div class="mb-4 flex items-center gap-2">
                    <input
                        wire:model.live="selectAll"
                        type="checkbox"
                        id="selectAll"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                    >
                    <label for="selectAll" class="text-sm text-gray-700">Select all books</label>
                </div>
            @endif

            {{-- Books Grid --}}
            @if($books->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No books</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a book or importing from GoodReads.</p>
                    <div class="mt-6 flex justify-center gap-3">
                        <a href="{{ route('books.import') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Import from GoodReads
                        </a>
                        <a href="{{ route('books.create') }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Add Book
                        </a>
                    </div>
                </div>
            @else
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                    wire:loading.class="opacity-50 pointer-events-none"
                    wire:target="gotoPage, previousPage, nextPage, search, status"
                >
                    @foreach($books as $book)
                        <article wire:key="book-{{ $book->id }}" class="group relative flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                            {{-- Selection Checkbox --}}
                            <div class="absolute top-2 left-2 z-10">
                                <input
                                    wire:model.live="selected"
                                    type="checkbox"
                                    value="{{ $book->id }}"
                                    class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-600 bg-white/90 shadow"
                                >
                            </div>

                            {{-- Book Cover or Placeholder --}}
                            <div class="aspect-[2/3] bg-gray-100 flex items-center justify-center overflow-hidden">
                                @if($book->cover_url)
                                    <img
                                        src="{{ $book->cover_url }}"
                                        alt=""
                                        class="h-full w-full object-cover"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >
                                    <div class="h-full w-full items-center justify-center hidden">
                                        <svg class="h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                    </div>
                                @elseif($book->isbn || $book->isbn13)
                                    {{-- Has ISBN but no cover yet - loading state --}}
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <svg class="h-10 w-10 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                        <span class="mt-2 text-xs">Loading cover...</span>
                                    </div>
                                @else
                                    {{-- No ISBN - will never have a cover --}}
                                    <svg class="h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Book Info --}}
                            <div class="flex flex-1 flex-col p-4">
                                <h3 class="text-sm font-medium text-gray-900 line-clamp-2">
                                    <a href="{{ route('books.show', $book) }}" class="focus:outline-none">
                                        <span class="absolute inset-0" aria-hidden="true"></span>
                                        {{ $book->title }}
                                    </a>
                                </h3>
                                @if($book->author)
                                    <p class="mt-1 text-sm text-gray-500 line-clamp-1">{{ $book->author }}</p>
                                @endif

                                <div class="mt-auto pt-4">
                                    {{-- Status Badge --}}
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                        @switch($book->status->value)
                                            @case('want_to_read') bg-blue-100 text-blue-700 @break
                                            @case('reading') bg-yellow-100 text-yellow-700 @break
                                            @case('read') bg-green-100 text-green-700 @break
                                        @endswitch
                                    ">
                                        {{ $book->status->label() }}
                                    </span>

                                    {{-- Rating --}}
                                    @if($book->rating)
                                        <div class="mt-2 flex items-center" aria-label="Rating: {{ $book->rating }} out of 5 stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="h-4 w-4 {{ $i <= $book->rating ? 'text-yellow-400' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                                </svg>
                                            @endfor
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $books->links() }}
                </div>
            @endif
        </div>
    </main>

</div>
