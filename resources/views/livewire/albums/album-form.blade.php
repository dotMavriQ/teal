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
                            <a href="{{ route('listening.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Listening</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('albums.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Collection</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary" aria-current="page">
                                {{ $isEditing ? 'Edit Album' : 'Add Album' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                {{ $isEditing ? 'Edit Album' : 'Add Album' }}
            </h1>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form wire:submit="save" class="space-y-8">

                {{-- Album Details --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Album Details</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Core information about the album.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            {{-- Title --}}
                            <div class="sm:col-span-6">
                                <label for="title" class="block text-sm font-medium leading-6 text-theme-text-primary">
                                    Title <span class="text-theme-danger">*</span>
                                </label>
                                <div class="mt-2">
                                    <input wire:model="title" type="text" id="title" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6" required>
                                </div>
                                @error('title') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Artist --}}
                            <div class="sm:col-span-4">
                                <label for="artist" class="block text-sm font-medium leading-6 text-theme-text-primary">Artist</label>
                                <div class="mt-2">
                                    <input wire:model="artist" type="text" id="artist" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('artist') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Year --}}
                            <div class="sm:col-span-2">
                                <label for="year" class="block text-sm font-medium leading-6 text-theme-text-primary">Year</label>
                                <div class="mt-2">
                                    <input wire:model="year" type="number" id="year" min="1900" max="2100" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('year') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Genre --}}
                            <div class="sm:col-span-3">
                                <label for="genre" class="block text-sm font-medium leading-6 text-theme-text-primary">Genre</label>
                                <div class="mt-2">
                                    <input wire:model="genre" type="text" id="genre" placeholder="Rock, Electronic, Jazz" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                <p class="mt-1 text-xs text-theme-text-muted">Comma-separated</p>
                                @error('genre') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Styles --}}
                            <div class="sm:col-span-3">
                                <label for="styles" class="block text-sm font-medium leading-6 text-theme-text-primary">Styles</label>
                                <div class="mt-2">
                                    <input wire:model="styles" type="text" id="styles" placeholder="Shoegaze, Dream Pop" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                <p class="mt-1 text-xs text-theme-text-muted">Comma-separated</p>
                                @error('styles') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Format --}}
                            <div class="sm:col-span-3">
                                <label for="format" class="block text-sm font-medium leading-6 text-theme-text-primary">Format</label>
                                <div class="mt-2">
                                    <input wire:model="format" type="text" id="format" placeholder="e.g. Vinyl, CD, Cassette" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('format') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Label --}}
                            <div class="sm:col-span-3">
                                <label for="label" class="block text-sm font-medium leading-6 text-theme-text-primary">Label</label>
                                <div class="mt-2">
                                    <input wire:model="label" type="text" id="label" placeholder="e.g. Sub Pop" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('label') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Country --}}
                            <div class="sm:col-span-3">
                                <label for="country" class="block text-sm font-medium leading-6 text-theme-text-primary">Country</label>
                                <div class="mt-2">
                                    <input wire:model="country" type="text" id="country" placeholder="e.g. US" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('country') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Cover URL --}}
                            <div class="sm:col-span-6">
                                <label for="cover_url" class="block text-sm font-medium leading-6 text-theme-text-primary">Cover Image URL</label>
                                <div class="mt-2">
                                    <input wire:model="cover_url" type="url" id="cover_url" placeholder="https://..." class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('cover_url') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Your Experience --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Your Experience</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Track your listening status, ownership, rating, and notes.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            {{-- Status --}}
                            <div class="sm:col-span-3">
                                <label for="status" class="block text-sm font-medium leading-6 text-theme-text-primary">Status</label>
                                <div class="mt-2">
                                    <select wire:model="status" id="status" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('status') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Ownership --}}
                            <div class="sm:col-span-3">
                                <label for="ownership" class="block text-sm font-medium leading-6 text-theme-text-primary">Ownership</label>
                                <div class="mt-2">
                                    <select wire:model="ownership" id="ownership" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                        @foreach($ownershipStatuses as $ownershipOption)
                                            <option value="{{ $ownershipOption->value }}">{{ $ownershipOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('ownership') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Rating (1-5 stars) --}}
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-theme-text-primary">Rating</label>
                                <div class="mt-2 flex items-center gap-1" role="group" aria-label="Rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button
                                            wire:click="$set('rating', {{ $rating === $i ? 'null' : $i }})"
                                            type="button"
                                            class="focus:outline-none focus:ring-2 focus:ring-theme-accent-primary rounded"
                                            aria-label="Rate {{ $i }} out of 5"
                                        >
                                            <svg class="h-8 w-8 {{ $i <= ($rating ?? 0) ? 'text-theme-star-filled' : 'text-theme-star-empty hover:text-theme-star-filled/50' }} transition-colors" viewBox="0 0 20 20" fill="currentColor">
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
                                    <textarea wire:model="notes" id="notes" rows="4" placeholder="Your thoughts about this album..." class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"></textarea>
                                </div>
                                @error('notes') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- External IDs --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">External IDs</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Link this album to Discogs.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Discogs ID --}}
                            <div class="sm:col-span-3">
                                <label for="discogs_id" class="block text-sm font-medium leading-6 text-theme-text-primary">Discogs Release ID</label>
                                <div class="mt-2">
                                    <input wire:model="discogs_id" type="number" id="discogs_id" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('discogs_id') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Discogs Master ID --}}
                            <div class="sm:col-span-3">
                                <label for="discogs_master_id" class="block text-sm font-medium leading-6 text-theme-text-primary">Discogs Master ID</label>
                                <div class="mt-2">
                                    <input wire:model="discogs_master_id" type="number" id="discogs_master_id" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('discogs_master_id') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-x-6">
                    <a href="{{ $isEditing && isset($album) ? route('albums.show', $album) : route('albums.index') }}" class="text-sm font-semibold leading-6 text-theme-text-primary">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-md btn-primary px-3 py-2 text-sm font-semibold shadow-sm">
                        {{ $isEditing ? 'Update Album' : 'Create Album' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
