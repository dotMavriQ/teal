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
                            <a href="{{ route('books.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Books</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500 line-clamp-1" aria-current="page">{{ $book->title }}</span>
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
                    <div class="aspect-[2/3] overflow-hidden rounded-lg bg-gray-100 flex items-center justify-center">
                        @if($book->cover_url)
                            <img src="{{ $book->cover_url }}" alt="Cover of {{ $book->title }}" class="h-full w-full object-cover">
                        @else
                            <svg class="h-24 w-24 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        @endif
                    </div>
                </div>

                {{-- Book Details --}}
                <div class="mt-10 lg:col-span-2 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $book->title }}</h1>

                    @if($book->author)
                        <p class="mt-2 text-xl text-gray-600">by {{ $book->author }}</p>
                    @endif

                    {{-- Status & Actions --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        {{-- Status Dropdown --}}
                        <div>
                            <label for="status" class="sr-only">Reading status</label>
                            <select
                                wire:change="updateStatus($event.target.value)"
                                id="status"
                                class="rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6"
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
                                    class="focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                                    aria-label="Rate {{ $i }} out of 5 stars"
                                >
                                    <svg class="h-6 w-6 {{ $i <= ($book->rating ?? 0) ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-200' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        <div class="flex-1"></div>

                        {{-- Edit & Delete --}}
                        <a href="{{ route('books.edit', $book) }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Edit
                        </a>
                        <button
                            wire:click="deleteBook"
                            wire:confirm="Are you sure you want to delete this book?"
                            type="button"
                            class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                        >
                            Delete
                        </button>
                    </div>

                    {{-- Book Metadata --}}
                    <dl class="mt-8 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @if($book->publisher)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Publisher</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->publisher }}</dd>
                            </div>
                        @endif

                        @if($book->published_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Published</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->published_date->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($book->page_count)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Pages</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($book->page_count) }}</dd>
                            </div>
                        @endif

                        @if($book->isbn13 || $book->isbn)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ISBN</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->isbn13 ?? $book->isbn }}</dd>
                            </div>
                        @endif

                        @if($book->date_started)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Started</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->date_started->format('F j, Y') }}</dd>
                            </div>
                        @endif

                        @if($book->date_finished)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Finished</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $book->date_finished->format('F j, Y') }}</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Description --}}
                    @if($book->description)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-gray-900">Description</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-gray-500">
                                {!! nl2br(e($book->description)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if($book->notes)
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-gray-900">My Notes</h2>
                            <div class="mt-2 prose prose-sm max-w-none text-gray-500">
                                {!! nl2br(e($book->notes)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
