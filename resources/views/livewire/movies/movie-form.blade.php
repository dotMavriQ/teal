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
                            <a href="{{ route('movies.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Movies</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary" aria-current="page">
                                {{ $isEditing ? 'Edit' : 'Add Movie' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                {{ $isEditing ? 'Edit Movie' : 'Add Movie' }}
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
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-2">
                                    <input
                                        wire:model="title"
                                        type="text"
                                        id="title"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                        required
                                    >
                                </div>
                                @error('title')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Original Title --}}
                            <div class="sm:col-span-4">
                                <label for="original_title" class="block text-sm font-medium leading-6 text-theme-text-primary">Original Title</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="original_title"
                                        type="text"
                                        id="original_title"
                                        placeholder="If different from title"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="sm:col-span-2">
                                <label for="status" class="block text-sm font-medium leading-6 text-theme-text-primary">Status</label>
                                <div class="mt-2">
                                    <select
                                        wire:model="status"
                                        id="status"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Director --}}
                            <div class="sm:col-span-4">
                                <label for="director" class="block text-sm font-medium leading-6 text-theme-text-primary">Director</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="director"
                                        type="text"
                                        id="director"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('director')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Year --}}
                            <div class="sm:col-span-2">
                                <label for="year" class="block text-sm font-medium leading-6 text-theme-text-primary">Year</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="year"
                                        type="number"
                                        id="year"
                                        min="1888"
                                        max="2100"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('year')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
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
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    ></textarea>
                                </div>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Movie Details --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Movie Details</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Optional information about the movie.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Runtime --}}
                            <div class="sm:col-span-2">
                                <label for="runtime_minutes" class="block text-sm font-medium leading-6 text-theme-text-primary">Runtime (min)</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="runtime_minutes"
                                        type="number"
                                        id="runtime_minutes"
                                        min="1"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- Genres --}}
                            <div class="sm:col-span-4">
                                <label for="genres" class="block text-sm font-medium leading-6 text-theme-text-primary">Genres</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="genres"
                                        type="text"
                                        id="genres"
                                        placeholder="Action, Drama, Sci-Fi"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                <p class="mt-1 text-xs text-theme-text-muted">Comma-separated list of genres</p>
                            </div>

                            {{-- IMDb ID --}}
                            <div class="sm:col-span-3">
                                <label for="imdb_id" class="block text-sm font-medium leading-6 text-theme-text-primary">IMDb ID</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="imdb_id"
                                        type="text"
                                        id="imdb_id"
                                        placeholder="tt1234567"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                            </div>

                            {{-- Release Date --}}
                            <div class="sm:col-span-3">
                                <label for="release_date" class="block text-sm font-medium leading-6 text-theme-text-primary">Release Date</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="release_date"
                                        type="text"
                                        id="release_date"
                                        placeholder="DD/MM/YYYY"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('release_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Poster URL --}}
                            <div class="sm:col-span-6">
                                <label for="poster_url" class="block text-sm font-medium leading-6 text-theme-text-primary">Poster Image URL</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="poster_url"
                                        type="url"
                                        id="poster_url"
                                        placeholder="https://..."
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('poster_url')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Watching Progress --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Your Progress</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Track when you watched this movie.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Date Watched --}}
                            <div class="sm:col-span-3">
                                <label for="date_watched" class="block text-sm font-medium leading-6 text-theme-text-primary">Date Watched</label>
                                <div class="mt-2">
                                    <input
                                        wire:model="date_watched"
                                        type="text"
                                        id="date_watched"
                                        placeholder="DD/MM/YYYY"
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    >
                                </div>
                                @error('date_watched')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Rating (1-10 numbered buttons) --}}
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-theme-text-primary">Rating</label>
                                <div class="mt-2 flex items-center gap-1" role="group" aria-label="Rating">
                                    @for($i = 1; $i <= 10; $i++)
                                        <button
                                            wire:click="$set('rating', {{ $rating === $i ? 'null' : $i }})"
                                            type="button"
                                            class="h-9 w-9 rounded-md text-sm font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-theme-accent-primary {{ $i <= ($rating ?? 0) ? 'bg-theme-star-filled text-theme-text-inverted' : 'bg-theme-bg-tertiary text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                                            aria-label="Rate {{ $i }} out of 10"
                                        >
                                            {{ $i }}
                                        </button>
                                    @endfor
                                    <span class="ml-2 text-sm text-theme-text-muted">
                                        {{ $rating ? $rating . ' / 10' : 'Not rated' }}
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
                                        placeholder="Your thoughts, favorite scenes, etc."
                                        class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                    ></textarea>
                                </div>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-x-6">
                    <a href="{{ $isEditing && $movie ? route('movies.show', $movie) : route('movies.index') }}" class="text-sm font-semibold leading-6 text-theme-text-primary">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="rounded-md btn-primary px-3 py-2 text-sm font-semibold shadow-sm"
                    >
                        {{ $isEditing ? 'Save Changes' : 'Add Movie' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
