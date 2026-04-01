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
                            <a href="{{ route('concerts.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Live</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary" aria-current="page">
                                {{ $isEditing ? 'Edit Concert' : 'Add Concert' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                {{ $isEditing ? 'Edit Concert' : 'Add Concert' }}
            </h1>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form wire:submit="save" class="space-y-8">

                {{-- Event Details --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Event Details</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Core information about the concert event.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            {{-- Artist --}}
                            <div class="sm:col-span-6">
                                <label for="artist" class="block text-sm font-medium leading-6 text-theme-text-primary">
                                    Artist <span class="text-theme-danger">*</span>
                                </label>
                                <div class="mt-2">
                                    <input wire:model="artist" type="text" id="artist" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6" required>
                                </div>
                                @error('artist') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Tour Name --}}
                            <div class="sm:col-span-6">
                                <label for="tour_name" class="block text-sm font-medium leading-6 text-theme-text-primary">Tour Name</label>
                                <div class="mt-2">
                                    <input wire:model="tour_name" type="text" id="tour_name" placeholder="e.g. Eras Tour" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('tour_name') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Venue --}}
                            <div class="sm:col-span-4">
                                <label for="venue" class="block text-sm font-medium leading-6 text-theme-text-primary">Venue</label>
                                <div class="mt-2">
                                    <input wire:model="venue" type="text" id="venue" placeholder="e.g. Madison Square Garden" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('venue') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Event Date --}}
                            <div class="sm:col-span-2">
                                <label for="event_date" class="block text-sm font-medium leading-6 text-theme-text-primary">Event Date</label>
                                <div class="mt-2">
                                    <input wire:model="event_date" type="text" id="event_date" placeholder="DD/MM/YYYY" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('event_date') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- City --}}
                            <div class="sm:col-span-3">
                                <label for="city" class="block text-sm font-medium leading-6 text-theme-text-primary">City</label>
                                <div class="mt-2">
                                    <input wire:model="city" type="text" id="city" placeholder="e.g. New York" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('city') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Country --}}
                            <div class="sm:col-span-3">
                                <label for="country" class="block text-sm font-medium leading-6 text-theme-text-primary">Country</label>
                                <div class="mt-2">
                                    <input wire:model="country" type="text" id="country" placeholder="e.g. USA" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
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
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Track your attendance status, rating, and personal notes.</p>

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

                            {{-- Rating (1-10 numbered buttons) --}}
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-theme-text-primary">Rating</label>
                                <div class="mt-2 flex items-center gap-1 flex-wrap" role="group" aria-label="Rating">
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
                                    <textarea wire:model="notes" id="notes" rows="4" placeholder="Your thoughts about the show..." class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"></textarea>
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
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Link this concert to external databases.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Setlist.fm ID --}}
                            <div class="sm:col-span-3">
                                <label for="setlist_fm_id" class="block text-sm font-medium leading-6 text-theme-text-primary">Setlist.fm ID</label>
                                <div class="mt-2">
                                    <input wire:model="setlist_fm_id" type="text" id="setlist_fm_id" placeholder="e.g. 3bd6e97" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('setlist_fm_id') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Artist MusicBrainz ID --}}
                            <div class="sm:col-span-3">
                                <label for="artist_mbid" class="block text-sm font-medium leading-6 text-theme-text-primary">Artist MusicBrainz ID</label>
                                <div class="mt-2">
                                    <input wire:model="artist_mbid" type="text" id="artist_mbid" placeholder="e.g. 65f4f0c5-ef9e-490c-aee3-909e7ae6b2ab" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('artist_mbid') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-x-6">
                    <a href="{{ $isEditing && isset($concert) ? route('concerts.show', $concert) : route('concerts.index') }}" class="text-sm font-semibold leading-6 text-theme-text-primary">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-md btn-primary px-3 py-2 text-sm font-semibold shadow-sm">
                        {{ $isEditing ? 'Update Concert' : 'Create Concert' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
