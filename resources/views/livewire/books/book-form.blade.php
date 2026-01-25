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
                            <span class="ml-4 text-sm font-medium text-gray-500" aria-current="page">
                                {{ $isEditing ? 'Edit' : 'Add Book' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">
                {{ $isEditing ? 'Edit Book' : 'Add Book' }}
            </h1>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form wire:submit="save" class="space-y-8">
                {{-- Basic Info --}}
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Title --}}
                            <div class="sm:col-span-6">
                                <label for="title" class="block text-sm font-medium leading-6 text-gray-900">
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-2">
                                    <input
                                        wire:model="title"
                                        type="text"
                                        id="title"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                        required
                                    >
                                </div>
                                @error('title')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Author --}}
                            <div class="sm:col-span-4">
                                <label for="author" class="block text-sm font-medium leading-6 text-gray-900">Author</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="author"
                                        type="text"
                                        id="author"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('author')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="sm:col-span-2">
                                <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                                <div class="mt-2">
                                    <select
                                        wire:model="status"
                                        id="status"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="sm:col-span-6">
                                <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                                <div class="mt-2">
                                    <textarea
                                        wire:model="description"
                                        id="description"
                                        rows="4"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    ></textarea>
                                </div>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Publication Details --}}
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Publication Details</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Optional information about the book.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Publisher --}}
                            <div class="sm:col-span-3">
                                <label for="publisher" class="block text-sm font-medium leading-6 text-gray-900">Publisher</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="publisher"
                                        type="text"
                                        id="publisher"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- Published Date --}}
                            <div class="sm:col-span-3">
                                <label for="published_date" class="block text-sm font-medium leading-6 text-gray-900">Published Date</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="published_date"
                                        type="text"
                                        id="published_date"
                                        placeholder="DD/MM/YYYY"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('published_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Page Count --}}
                            <div class="sm:col-span-2">
                                <label for="page_count" class="block text-sm font-medium leading-6 text-gray-900">Pages</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="page_count"
                                        type="number"
                                        id="page_count"
                                        min="1"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- ISBN --}}
                            <div class="sm:col-span-2">
                                <label for="isbn" class="block text-sm font-medium leading-6 text-gray-900">ISBN-10</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="isbn"
                                        type="text"
                                        id="isbn"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- ISBN-13 --}}
                            <div class="sm:col-span-2">
                                <label for="isbn13" class="block text-sm font-medium leading-6 text-gray-900">ISBN-13</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="isbn13"
                                        type="text"
                                        id="isbn13"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- Cover URL --}}
                            <div class="sm:col-span-6">
                                <label for="cover_url" class="block text-sm font-medium leading-6 text-gray-900">Cover Image URL</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="cover_url"
                                        type="url"
                                        id="cover_url"
                                        placeholder="https://..."
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('cover_url')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reading Progress --}}
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Reading Progress</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Track when you started and finished reading.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Date Started --}}
                            <div class="sm:col-span-3">
                                <label for="date_started" class="block text-sm font-medium leading-6 text-gray-900">Date Started</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="date_started"
                                        type="text"
                                        id="date_started"
                                        placeholder="DD/MM/YYYY"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- Date Finished (Date Recorded) --}}
                            <div class="sm:col-span-3">
                                <label for="date_finished" class="block text-sm font-medium leading-6 text-gray-900">Date Read</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="date_finished"
                                        type="text"
                                        id="date_finished"
                                        placeholder="DD/MM/YYYY"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('date_finished')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Rating --}}
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-gray-900">Rating</label>
                                <div class="mt-2 flex items-center gap-1" role="group" aria-label="Rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button
                                            wire:click="$set('rating', {{ $rating === $i ? 'null' : $i }})"
                                            type="button"
                                            class="focus:outline-none focus:ring-2 focus:ring-blue-500 rounded p-1"
                                            aria-label="Rate {{ $i }} out of 5 stars"
                                        >
                                            <svg class="h-8 w-8 {{ $i <= ($rating ?? 0) ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-200' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-500">
                                        {{ $rating ? $rating . ' / 5' : 'Not rated' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="sm:col-span-6">
                                <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Personal Notes</label>
                                <div class="mt-2">
                                    <textarea
                                        wire:model="notes"
                                        id="notes"
                                        rows="4"
                                        placeholder="Your thoughts, favorite quotes, etc."
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    ></textarea>
                                </div>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tags --}}
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-gray-900">Tags</label>

                                {{-- Current Tags --}}
                                @if(count($tags) > 0)
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($tags as $index => $tag)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">
                                                {{ $tag }}
                                                <button
                                                    wire:click="removeTag({{ $index }})"
                                                    type="button"
                                                    class="ml-1 text-blue-400 hover:text-blue-600"
                                                >
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Add New Tag --}}
                                <div class="mt-3 flex gap-2">
                                    <input
                                        wire:model="newTag"
                                        wire:keydown.enter.prevent="addTag"
                                        type="text"
                                        placeholder="Add a new tag..."
                                        class="block flex-1 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                    <button
                                        wire:click="addTag"
                                        type="button"
                                        class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                    >
                                        Add
                                    </button>
                                </div>

                                {{-- Existing Tags --}}
                                @php
                                    $unusedTags = array_diff($availableTags, $tags);
                                @endphp
                                @if(count($unusedTags) > 0)
                                    <div class="mt-3">
                                        <p class="text-xs text-gray-500 mb-2">Click to add existing tag:</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($unusedTags as $availableTag)
                                                <button
                                                    wire:click="addExistingTag('{{ $availableTag }}')"
                                                    type="button"
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors"
                                                >
                                                    + {{ $availableTag }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-x-6">
                    <a href="{{ $isEditing && $book ? route('books.show', $book) : route('books.index') }}" class="text-sm font-semibold leading-6 text-gray-900">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                    >
                        {{ $isEditing ? 'Save Changes' : 'Add Book' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
