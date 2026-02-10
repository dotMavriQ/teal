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
                            <a href="{{ route('anime.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Anime</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary" aria-current="page">
                                {{ $isEditing ? 'Edit' : 'Add Anime' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                {{ $isEditing ? 'Edit Anime' : 'Add Anime' }}
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
                                    <input wire:model="title" type="text" id="title" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6" required>
                                </div>
                                @error('title') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Original Title --}}
                            <div class="sm:col-span-4">
                                <label for="original_title" class="block text-sm font-medium leading-6 text-theme-text-primary">Original Title</label>
                                <div class="mt-2">
                                    <input wire:model="original_title" type="text" id="original_title" placeholder="Japanese title" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="sm:col-span-2">
                                <label for="status" class="block text-sm font-medium leading-6 text-theme-text-primary">Status</label>
                                <div class="mt-2">
                                    <select wire:model="status" id="status" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Year --}}
                            <div class="sm:col-span-2">
                                <label for="year" class="block text-sm font-medium leading-6 text-theme-text-primary">Year</label>
                                <div class="mt-2">
                                    <input wire:model="year" type="number" id="year" min="1900" max="2100" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('year') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Media Type --}}
                            <div class="sm:col-span-2">
                                <label for="media_type" class="block text-sm font-medium leading-6 text-theme-text-primary">Media Type</label>
                                <div class="mt-2">
                                    <select wire:model="media_type" id="media_type" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                        <option value="">—</option>
                                        <option value="TV">TV</option>
                                        <option value="Movie">Movie</option>
                                        <option value="OVA">OVA</option>
                                        <option value="ONA">ONA</option>
                                        <option value="Special">Special</option>
                                        <option value="Music">Music</option>
                                    </select>
                                </div>
                            </div>

                            {{-- MAL ID --}}
                            <div class="sm:col-span-2">
                                <label for="mal_id" class="block text-sm font-medium leading-6 text-theme-text-primary">MAL ID</label>
                                <div class="mt-2">
                                    <input wire:model="mal_id" type="number" id="mal_id" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="sm:col-span-6">
                                <label for="description" class="block text-sm font-medium leading-6 text-theme-text-primary">Description</label>
                                <div class="mt-2">
                                    <textarea wire:model="description" id="description" rows="4" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"></textarea>
                                </div>
                                @error('description') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Anime Details --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Anime Details</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Optional information about the anime.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Episodes Total --}}
                            <div class="sm:col-span-2">
                                <label for="episodes_total" class="block text-sm font-medium leading-6 text-theme-text-primary">Total Episodes</label>
                                <div class="mt-2">
                                    <input wire:model="episodes_total" type="number" id="episodes_total" min="0" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                            </div>

                            {{-- Episodes Watched --}}
                            <div class="sm:col-span-2">
                                <label for="episodes_watched" class="block text-sm font-medium leading-6 text-theme-text-primary">Episodes Watched</label>
                                <div class="mt-2">
                                    <input wire:model="episodes_watched" type="number" id="episodes_watched" min="0" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                            </div>

                            {{-- Runtime --}}
                            <div class="sm:col-span-2">
                                <label for="runtime_minutes" class="block text-sm font-medium leading-6 text-theme-text-primary">Ep. Length (min)</label>
                                <div class="mt-2">
                                    <input wire:model="runtime_minutes" type="number" id="runtime_minutes" min="1" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                            </div>

                            {{-- Genres --}}
                            <div class="sm:col-span-3">
                                <label for="genres" class="block text-sm font-medium leading-6 text-theme-text-primary">Genres</label>
                                <div class="mt-2">
                                    <input wire:model="genres" type="text" id="genres" placeholder="Action, Comedy, Fantasy" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                <p class="mt-1 text-xs text-theme-text-muted">Comma-separated</p>
                            </div>

                            {{-- Studios --}}
                            <div class="sm:col-span-3">
                                <label for="studios" class="block text-sm font-medium leading-6 text-theme-text-primary">Studios</label>
                                <div class="mt-2">
                                    <input wire:model="studios" type="text" id="studios" placeholder="MAPPA, Bones" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                <p class="mt-1 text-xs text-theme-text-muted">Comma-separated</p>
                            </div>

                            {{-- Poster URL --}}
                            <div class="sm:col-span-6">
                                <label for="poster_url" class="block text-sm font-medium leading-6 text-theme-text-primary">Poster Image URL</label>
                                <div class="mt-2">
                                    <input wire:model="poster_url" type="url" id="poster_url" placeholder="https://..." class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('poster_url') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Watching Progress --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Your Progress</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Track when you watched this anime.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- Date Started --}}
                            <div class="sm:col-span-3">
                                <label for="date_started" class="block text-sm font-medium leading-6 text-theme-text-primary">Date Started</label>
                                <div class="mt-2">
                                    <input wire:model="date_started" type="text" id="date_started" placeholder="DD/MM/YYYY" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('date_started') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Date Finished --}}
                            <div class="sm:col-span-3">
                                <label for="date_finished" class="block text-sm font-medium leading-6 text-theme-text-primary">Date Finished</label>
                                <div class="mt-2">
                                    <input wire:model="date_finished" type="text" id="date_finished" placeholder="DD/MM/YYYY" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('date_finished') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
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
                                    <textarea wire:model="notes" id="notes" rows="4" placeholder="Your thoughts..." class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"></textarea>
                                </div>
                                @error('notes') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-x-6">
                    <a href="{{ $isEditing && $anime ? route('anime.show', $anime) : route('anime.index') }}" class="text-sm font-semibold leading-6 text-theme-text-primary">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-md btn-primary px-3 py-2 text-sm font-semibold shadow-sm">
                        {{ $isEditing ? 'Save Changes' : 'Add Anime' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
