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
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-border-secondary" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('books.index') }}" class="ml-4 text-sm font-medium text-theme-text-muted hover:text-theme-text-primary">Books</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-border-secondary" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-muted" aria-current="page">
                                {{ $isEditing ? 'Edit' : 'Add Book' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                {{ $isEditing ? 'Edit Book' : 'Add Book' }}
            </h1>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form wire:submit="save" class="space-y-8">
                {{-- Basic Info --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Title --}}
                            <div class="sm:col-span-6">
                                <label for="title" class="block text-sm font-medium leading-6 text-theme-text-primary">
                                    Title <span class="text-theme-danger">*</span>
                                </label>
                                <div class="mt-2">
                                    <input
                                        wire:model="title"
                                        type="text"
                                        id="title"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                        required
                                    >
                                </div>
                                @error('title')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Author --}}
                            <div class="sm:col-span-4">
                                <label for="author" class="block text-sm font-medium leading-6 text-theme-text-primary">Author</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="author"
                                        type="text"
                                        id="author"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('author')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="sm:col-span-2">
                                <label for="status" class="block text-sm font-medium leading-6 text-theme-text-primary">Status</label>
                                <div class="mt-2">
                                    <select
                                        wire:model="status"
                                        id="status"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="sm:col-span-6">
                                <label for="description" class="block text-sm font-medium leading-6 text-theme-text-primary">Description</label>
                                <div class="mt-2">
                                    <textarea
                                        wire:model="description"
                                        id="description"
                                        rows="4"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    ></textarea>
                                </div>
                                @error('description')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Publication Details --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Publication Details</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Optional information about the book.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Publisher --}}
                            <div class="sm:col-span-3">
                                <label for="publisher" class="block text-sm font-medium leading-6 text-theme-text-primary">Publisher</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="publisher"
                                        type="text"
                                        id="publisher"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- Published Date --}}
                            <div class="sm:col-span-3">
                                <label for="published_date" class="block text-sm font-medium leading-6 text-theme-text-primary">Published Date</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="published_date"
                                        type="text"
                                        id="published_date"
                                        placeholder="DD/MM/YYYY"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('published_date')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Page Count --}}
                            <div class="sm:col-span-2">
                                <label for="page_count" class="block text-sm font-medium leading-6 text-theme-text-primary">Pages</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="page_count"
                                        type="number"
                                        id="page_count"
                                        min="1"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- ISBN --}}
                            <div class="sm:col-span-2">
                                <label for="isbn" class="block text-sm font-medium leading-6 text-theme-text-primary">ISBN-10</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="isbn"
                                        type="text"
                                        id="isbn"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- ISBN-13 --}}
                            <div class="sm:col-span-2">
                                <label for="isbn13" class="block text-sm font-medium leading-6 text-theme-text-primary">ISBN-13</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="isbn13"
                                        type="text"
                                        id="isbn13"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- Cover URL --}}
                            <div class="sm:col-span-6">
                                <label for="cover_url" class="block text-sm font-medium leading-6 text-theme-text-primary">Cover Image URL</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="cover_url"
                                        type="url"
                                        id="cover_url"
                                        placeholder="https://..."
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('cover_url')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reading Progress --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Reading Progress</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Track when you started and finished reading.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Date Started --}}
                            <div class="sm:col-span-3">
                                <label for="date_started" class="block text-sm font-medium leading-6 text-theme-text-primary">Date Started</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="date_started"
                                        type="text"
                                        id="date_started"
                                        placeholder="DD/MM/YYYY"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- Date Finished (Date Recorded) --}}
                            <div class="sm:col-span-3">
                                <label for="date_finished" class="block text-sm font-medium leading-6 text-theme-text-primary">Date Read</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="date_finished"
                                        type="text"
                                        id="date_finished"
                                        placeholder="DD/MM/YYYY"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('date_finished')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Reading Progress (Current Page) --}}
                            @if($status === 'reading' && $page_count)
                                <div class="sm:col-span-6">
                                    <label for="current_page" class="block text-sm font-medium leading-6 text-theme-text-primary">Reading Progress</label>
                                    <div class="mt-2">
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-theme-text-secondary">Page</span>
                                                <input
                                                    wire:model.live="current_page"
                                                    type="number"
                                                    id="current_page"
                                                    min="0"
                                                    max="{{ $page_count }}"
                                                    class="w-24 rounded-md border-0 py-1.5 text-center text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                                >
                                                <span class="text-sm text-theme-text-secondary">of {{ $page_count }}</span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-1 h-3 bg-theme-bg-tertiary rounded-full overflow-hidden">
                                                        <div
                                                            class="h-full bg-gradient-to-r from-theme-accent-primary to-theme-status-reading rounded-full transition-all duration-300"
                                                            style="width: {{ $page_count > 0 && $current_page ? min(100, round(($current_page / $page_count) * 100)) : 0 }}%"
                                                        ></div>
                                                    </div>
                                                    <span class="text-sm font-medium text-theme-text-primary min-w-[3rem] text-right">
                                                        {{ $page_count > 0 && $current_page ? min(100, round(($current_page / $page_count) * 100)) : 0 }}%
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Quick progress buttons --}}
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach([10, 25, 50, 75, 100] as $percent)
                                                <button
                                                    wire:click="$set('current_page', {{ (int) round($page_count * $percent / 100) }})"
                                                    type="button"
                                                    class="px-2.5 py-1 text-xs font-medium rounded-md {{ $current_page == (int) round($page_count * $percent / 100) ? 'bg-theme-accent-primary text-white' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }} transition-colors"
                                                >
                                                    {{ $percent }}%
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('current_page')
                                        <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            {{-- Rating --}}
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-theme-text-primary">Rating</label>
                                <div class="mt-2 flex items-center gap-1" role="group" aria-label="Rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button
                                            wire:click="$set('rating', {{ $rating === $i ? 'null' : $i }})"
                                            type="button"
                                            class="focus:outline-none focus:ring-2 focus:ring-theme-accent-primary rounded p-1"
                                            aria-label="Rate {{ $i }} out of 5 stars"
                                        >
                                            <svg class="h-8 w-8 {{ $i <= ($rating ?? 0) ? 'text-yellow-400' : 'text-theme-text-muted hover:text-yellow-200' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endfor
                                    <span class="ml-2 text-sm text-theme-text-muted">
                                        {{ $rating ? $rating . ' / 5' : 'Not rated' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="sm:col-span-6">
                                <label for="notes" class="block text-sm font-medium leading-6 text-theme-text-primary">Personal Notes</label>
                                <div class="mt-2">
                                    <textarea
                                        wire:model="notes"
                                        id="notes"
                                        rows="4"
                                        placeholder="Your thoughts, favorite quotes, etc."
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    ></textarea>
                                </div>
                                @error('notes')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tags --}}
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-theme-text-primary">Tags</label>

                                {{-- Current Tags --}}
                                @if(count($tags) > 0)
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($tags as $index => $tag)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-theme-accent-primary/10 px-3 py-1 text-sm font-medium text-theme-accent-primary">
                                                {{ $tag }}
                                                <button
                                                    wire:click="removeTag({{ $index }})"
                                                    type="button"
                                                    class="ml-1 text-theme-accent-primary/60 hover:text-theme-accent-primary"
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
                                        class="block flex-1 rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                    <button
                                        wire:click="addTag"
                                        type="button"
                                        class="rounded-md bg-theme-card-bg px-3 py-1.5 text-sm font-semibold text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover"
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
                                        <p class="text-xs text-theme-text-muted mb-2">Click to add existing tag:</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($unusedTags as $availableTag)
                                                <button
                                                    wire:click="addExistingTag('{{ $availableTag }}')"
                                                    type="button"
                                                    class="inline-flex items-center rounded-full bg-theme-bg-tertiary px-3 py-1 text-sm font-medium text-theme-text-secondary hover:bg-theme-bg-hover transition-colors"
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
                    <a href="{{ $isEditing && $book ? route('books.show', $book) : route('books.index') }}" class="text-sm font-semibold leading-6 text-theme-text-primary">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="rounded-md bg-theme-accent-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-theme-accent-primary/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-theme-accent-primary"
                    >
                        {{ $isEditing ? 'Save Changes' : 'Add Book' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
