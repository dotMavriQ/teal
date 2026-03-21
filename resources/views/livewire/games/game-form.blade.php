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
                            <a href="{{ route('games.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Games</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary" aria-current="page">
                                {{ $isEditing ? 'Edit' : 'Create' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                {{ $isEditing ? 'Edit Game' : 'Add Game' }}
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

                            {{-- Status --}}
                            <div class="sm:col-span-3">
                                <label for="status" class="block text-sm font-medium leading-6 text-theme-text-primary">Status</label>
                                <div class="mt-2">
                                    <select wire:model="status" id="status" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                        <option value="want_to_play">Want to Play</option>
                                        <option value="playing">Playing</option>
                                        <option value="played">Played</option>
                                    </select>
                                </div>
                                @error('status') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Ownership --}}
                            <div class="sm:col-span-3">
                                <label for="ownership" class="block text-sm font-medium leading-6 text-theme-text-primary">Ownership</label>
                                <div class="mt-2">
                                    <select wire:model="ownership" id="ownership" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                        <option value="">—</option>
                                        <option value="owned">Owned</option>
                                        <option value="previously_owned">Previously Owned</option>
                                        <option value="not_owned">Not Owned</option>
                                    </select>
                                </div>
                                @error('ownership') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Platform --}}
                            <div class="sm:col-span-6">
                                <label for="platformInput" class="block text-sm font-medium leading-6 text-theme-text-primary">Platform(s)</label>
                                <div class="mt-2 flex gap-2">
                                    <input wire:model="platformInput" type="text" id="platformInput" placeholder="e.g. PC, PS5" wire:keydown.enter.prevent="addPlatform" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                    <button type="button" wire:click="addPlatform" class="rounded-md btn-secondary px-3 py-2 text-sm font-semibold shadow-sm whitespace-nowrap">
                                        Add
                                    </button>
                                </div>
                                @if(!empty($platform))
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($platform as $index => $platform)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-theme-bg-secondary px-3 py-1 text-sm text-theme-text-primary ring-1 ring-theme-border-primary">
                                                {{ $platform }}
                                                <button type="button" wire:click="removePlatform({{ $index }})" class="ml-1 text-theme-text-muted hover:text-theme-danger focus:outline-none" aria-label="Remove {{ $platform }}">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                @error('platforms') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Genre --}}
                            <div class="sm:col-span-3">
                                <label for="genre" class="block text-sm font-medium leading-6 text-theme-text-primary">Genre</label>
                                <div class="mt-2">
                                    <input wire:model="genre" type="text" id="genre" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('genre') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Developer --}}
                            <div class="sm:col-span-3">
                                <label for="developer" class="block text-sm font-medium leading-6 text-theme-text-primary">Developer</label>
                                <div class="mt-2">
                                    <input wire:model="developer" type="text" id="developer" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('developer') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Publisher --}}
                            <div class="sm:col-span-3">
                                <label for="publisher" class="block text-sm font-medium leading-6 text-theme-text-primary">Publisher</label>
                                <div class="mt-2">
                                    <input wire:model="publisher" type="text" id="publisher" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('publisher') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Release Date --}}
                            <div class="sm:col-span-3">
                                <label for="release_date" class="block text-sm font-medium leading-6 text-theme-text-primary">Release Date</label>
                                <div class="mt-2">
                                    <input wire:model="release_date" type="text" id="release_date" placeholder="DD/MM/YYYY" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('release_date') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Cover URL --}}
                            <div class="sm:col-span-6">
                                <label for="cover_url" class="block text-sm font-medium leading-6 text-theme-text-primary">Cover Image URL</label>
                                <div class="mt-2">
                                    <input wire:model="cover_url" type="url" id="cover_url" placeholder="https://..." class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('cover_url') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
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

                {{-- Your Progress --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Your Progress</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Track your time and completion with this game.</p>

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

                            {{-- Hours Played --}}
                            <div class="sm:col-span-3">
                                <label for="hours_played" class="block text-sm font-medium leading-6 text-theme-text-primary">Hours Played</label>
                                <div class="mt-2">
                                    <input wire:model="hours_played" type="number" id="hours_played" min="0" step="0.1" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('hours_played') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Completion Percentage --}}
                            <div class="sm:col-span-3">
                                <label for="completion_percentage" class="block text-sm font-medium leading-6 text-theme-text-primary">Completion</label>
                                <div class="mt-2 flex items-center gap-2">
                                    <input wire:model="completion_percentage" type="number" id="completion_percentage" min="0" max="100" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                    <span class="text-sm text-theme-text-muted">%</span>
                                </div>
                                @error('completion_percentage') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
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

                {{-- External IDs --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">External IDs</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Link this game to external databases.</p>

                        <div class="mt-6 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            {{-- IGDB ID --}}
                            <div class="sm:col-span-2">
                                <label for="igdb_id" class="block text-sm font-medium leading-6 text-theme-text-primary">IGDB ID</label>
                                <div class="mt-2">
                                    <input wire:model="igdb_id" type="number" id="igdb_id" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('igdb_id') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- RAWG ID --}}
                            <div class="sm:col-span-2">
                                <label for="rawg_id" class="block text-sm font-medium leading-6 text-theme-text-primary">RAWG ID</label>
                                <div class="mt-2">
                                    <input wire:model="rawg_id" type="number" id="rawg_id" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('rawg_id') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- MobyGames ID --}}
                            <div class="sm:col-span-2">
                                <label for="mobygames_id" class="block text-sm font-medium leading-6 text-theme-text-primary">MobyGames ID</label>
                                <div class="mt-2">
                                    <input wire:model="mobygames_id" type="number" id="mobygames_id" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('mobygames_id') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-x-6">
                    <a href="{{ $isEditing && $game ? route('games.show', $game) : route('games.index') }}" class="text-sm font-semibold leading-6 text-theme-text-primary">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-md btn-primary px-3 py-2 text-sm font-semibold shadow-sm">
                        {{ $isEditing ? 'Update Game' : 'Create Game' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
