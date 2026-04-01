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
                            <a href="{{ route('playing.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Playing</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('board-games.index') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Board Games</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary" aria-current="page">
                                {{ $isEditing ? 'Edit Board Game' : 'Add Board Game' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                {{ $isEditing ? 'Edit Board Game' : 'Add Board Game' }}
            </h1>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form wire:submit="save" class="space-y-8">

                {{-- Basic Info --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Basic Information</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Core details about this board game.</p>

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

                            {{-- Genre (tag-style) --}}
                            <div class="sm:col-span-6">
                                <label for="genreInput" class="block text-sm font-medium leading-6 text-theme-text-primary">Genre(s)</label>
                                <div class="mt-2 flex gap-2">
                                    <input wire:model="genreInput" type="text" id="genreInput" placeholder="e.g. Strategy, Worker Placement" wire:keydown.enter.prevent="addGenre" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                    <button type="button" wire:click="addGenre" class="rounded-md btn-secondary px-3 py-2 text-sm font-semibold shadow-sm whitespace-nowrap">
                                        Add
                                    </button>
                                </div>
                                @if(!empty($genre))
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($genre as $index => $g)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-theme-bg-secondary px-3 py-1 text-sm text-theme-text-primary ring-1 ring-theme-border-primary">
                                                {{ $g }}
                                                <button type="button" wire:click="removeGenre({{ $index }})" class="ml-1 text-theme-text-muted hover:text-theme-danger focus:outline-none" aria-label="Remove {{ $g }}">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                @error('genre') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Designer --}}
                            <div class="sm:col-span-3">
                                <label for="designer" class="block text-sm font-medium leading-6 text-theme-text-primary">Designer</label>
                                <div class="mt-2">
                                    <input wire:model="designer" type="text" id="designer" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('designer') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Publisher --}}
                            <div class="sm:col-span-3">
                                <label for="publisher" class="block text-sm font-medium leading-6 text-theme-text-primary">Publisher</label>
                                <div class="mt-2">
                                    <input wire:model="publisher" type="text" id="publisher" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('publisher') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Year Published --}}
                            <div class="sm:col-span-2">
                                <label for="year_published" class="block text-sm font-medium leading-6 text-theme-text-primary">Year Published</label>
                                <div class="mt-2">
                                    <input wire:model="year_published" type="number" id="year_published" min="1900" max="2099" placeholder="e.g. 2023" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('year_published') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Min Players --}}
                            <div class="sm:col-span-2">
                                <label for="min_players" class="block text-sm font-medium leading-6 text-theme-text-primary">Min Players</label>
                                <div class="mt-2">
                                    <input wire:model="min_players" type="number" id="min_players" min="1" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('min_players') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Max Players --}}
                            <div class="sm:col-span-2">
                                <label for="max_players" class="block text-sm font-medium leading-6 text-theme-text-primary">Max Players</label>
                                <div class="mt-2">
                                    <input wire:model="max_players" type="number" id="max_players" min="1" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('max_players') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Playing Time --}}
                            <div class="sm:col-span-3">
                                <label for="playing_time" class="block text-sm font-medium leading-6 text-theme-text-primary">Playing Time (minutes)</label>
                                <div class="mt-2">
                                    <input wire:model="playing_time" type="number" id="playing_time" min="0" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('playing_time') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
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

                {{-- Your Collection --}}
                <div class="bg-theme-card-bg shadow-sm ring-1 ring-theme-border-primary sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h2 class="text-base font-semibold leading-7 text-theme-text-primary">Your Collection</h2>
                        <p class="mt-1 text-sm leading-6 text-theme-text-secondary">Track your status, plays, and rating for this game.</p>

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

                            {{-- Plays --}}
                            <div class="sm:col-span-3">
                                <label for="plays" class="block text-sm font-medium leading-6 text-theme-text-primary">Plays</label>
                                <div class="mt-2">
                                    <input wire:model="plays" type="number" id="plays" min="0" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('plays') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
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
                            {{-- BGG ID --}}
                            <div class="sm:col-span-3">
                                <label for="bgg_id" class="block text-sm font-medium leading-6 text-theme-text-primary">BoardGameGeek ID</label>
                                <div class="mt-2">
                                    <input wire:model="bgg_id" type="number" id="bgg_id" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6">
                                </div>
                                @error('bgg_id') <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-x-6">
                    <a href="{{ $isEditing && isset($boardGame) ? route('board-games.show', $boardGame) : route('board-games.index') }}" class="text-sm font-semibold leading-6 text-theme-text-primary">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-md btn-primary px-3 py-2 text-sm font-semibold shadow-sm">
                        {{ $isEditing ? 'Update Board Game' : 'Create Board Game' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
