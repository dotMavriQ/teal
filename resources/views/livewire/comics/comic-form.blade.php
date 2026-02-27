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
                            <a href="{{ route('reading.index') }}" class="ml-4 text-sm font-medium text-theme-text-muted hover:text-theme-text-primary">Reading</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-border-secondary" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('comics.index') }}" class="ml-4 text-sm font-medium text-theme-text-muted hover:text-theme-text-primary">Comics</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-border-secondary" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-muted" aria-current="page">
                                {{ $isEditing ? 'Edit' : 'Add Comic' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                {{ $isEditing ? 'Edit Comic' : 'Add Comic' }}
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

                            {{-- Publisher --}}
                            <div class="sm:col-span-4">
                                <label for="publisher" class="block text-sm font-medium leading-6 text-theme-text-primary">Publisher</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="publisher"
                                        type="text"
                                        id="publisher"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('publisher')
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

                            {{-- Creators --}}
                            <div class="sm:col-span-6">
                                <label for="creators" class="block text-sm font-medium leading-6 text-theme-text-primary">Creators</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="creators"
                                        type="text"
                                        id="creators"
                                        placeholder="Writer, Artist, etc."
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('creators')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Characters --}}
                            <div class="sm:col-span-6">
                                <label for="characters" class="block text-sm font-medium leading-6 text-theme-text-primary">Characters</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="characters"
                                        type="text"
                                        id="characters"
                                        placeholder="Batman, Superman, etc."
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('characters')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
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

                {{-- Volume Details --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Volume Details</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Additional metadata from Comic Vine.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Start Year --}}
                            <div class="sm:col-span-3">
                                <label for="start_year" class="block text-sm font-medium leading-6 text-theme-text-primary">Start Year</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="start_year"
                                        type="number"
                                        id="start_year"
                                        min="1900"
                                        max="2100"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('start_year')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Issue Count --}}
                            <div class="sm:col-span-3">
                                <label for="issue_count" class="block text-sm font-medium leading-6 text-theme-text-primary">Issue Count</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="issue_count"
                                        type="number"
                                        id="issue_count"
                                        min="0"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('issue_count')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Comic Vine Volume ID --}}
                            <div class="sm:col-span-3">
                                <label for="comicvine_volume_id" class="block text-sm font-medium leading-6 text-theme-text-primary">Comic Vine Volume ID</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="comicvine_volume_id"
                                        type="text"
                                        id="comicvine_volume_id"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('comicvine_volume_id')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Comic Vine URL --}}
                            <div class="sm:col-span-6">
                                <label for="comicvine_url" class="block text-sm font-medium leading-6 text-theme-text-primary">Comic Vine URL</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="comicvine_url"
                                        type="url"
                                        id="comicvine_url"
                                        placeholder="https://comicvine.gamespot.com/..."
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('comicvine_url')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
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
                                @error('date_started')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Date Finished --}}
                            <div class="sm:col-span-3">
                                <label for="date_finished" class="block text-sm font-medium leading-6 text-theme-text-primary">Date Finished</label>
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
                                        placeholder="Your thoughts, favorite arcs, etc."
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    ></textarea>
                                </div>
                                @error('notes')
                                    <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-x-6">
                    <a href="{{ $isEditing && $comic ? route('comics.show', $comic) : route('comics.index') }}" class="text-sm font-semibold leading-6 text-theme-text-primary">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="rounded-md bg-theme-accent-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-theme-accent-primary/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-theme-accent-primary"
                    >
                        {{ $isEditing ? 'Save Changes' : 'Add Comic' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
