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
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary" aria-current="page">Import</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                Import Movies
            </h1>
        </div>
    </header>

    <main class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(!$importResult)
                {{-- Format Specs --}}
                <div class="mb-8 rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg p-4 sm:p-6">
                    <h2 class="text-base font-semibold text-theme-text-primary mb-4">IMDB CSV Export</h2>
                    <div class="space-y-4 text-sm text-theme-text-secondary">
                        <div>
                            <h3 class="font-semibold text-theme-text-primary mb-2">Expected Columns:</h3>
                            <p class="text-xs font-mono bg-theme-bg-tertiary p-2 rounded overflow-x-auto mb-2">
                                Const, Your Rating, Date Rated, Title, URL, Title Type, IMDb Rating, Runtime (mins), Year, Genres, Num Votes, Release Date, Directors
                            </p>
                            <p class="text-theme-text-secondary">Required: Const (IMDb ID), Title. All other columns are optional.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme-text-primary mb-2">How to export from IMDb:</h3>
                            <ol class="list-decimal list-inside space-y-1 text-theme-text-secondary">
                                <li>Go to your IMDb Ratings page</li>
                                <li>Click the three-dot menu (top right)</li>
                                <li>Select "Export"</li>
                                <li>Download the CSV file</li>
                            </ol>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme-text-primary mb-2">Status Mapping:</h3>
                            <p class="text-theme-text-secondary">Rated movies are imported as "Watched". Unrated movies are imported as "Watchlist". Duplicates are detected by IMDb ID.</p>
                        </div>
                    </div>
                </div>

                {{-- File Upload --}}
                <div class="mb-8 rounded-lg border-2 border-dashed border-theme-border-secondary bg-theme-card-bg p-6 sm:p-8">
                    <form wire:submit="import" class="space-y-4">
                        <div>
                            <label for="file" class="block text-sm font-semibold text-theme-text-primary mb-2">
                                Choose CSV File
                            </label>
                            <input
                                type="file"
                                id="file"
                                wire:model="file"
                                accept=".csv,.txt"
                                class="block w-full text-sm text-theme-text-secondary file:rounded-md file:border-0 file:btn-primary file:px-4 file:py-2 file:text-sm file:font-semibold"
                                {{ $importing ? 'disabled' : '' }}
                            >
                            @error('file')
                                <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Options --}}
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="skipDuplicates"
                                wire:model="skipDuplicates"
                                class="h-4 w-4 rounded"
                                {{ $importing ? 'disabled' : '' }}
                            >
                            <label for="skipDuplicates" class="ml-3 text-sm text-theme-text-secondary">
                                Skip duplicate movies (matched by IMDb ID)
                            </label>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end pt-4">
                            <a
                                href="{{ route('movies.index') }}"
                                class="inline-flex items-center justify-center rounded-md btn-secondary px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                {{ !$file || $importing ? 'disabled' : '' }}
                                class="inline-flex items-center justify-center rounded-md btn-primary px-4 py-2 text-sm font-semibold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                @if($importing)
                                    <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Importing...
                                @else
                                    Import from IMDb
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Preview --}}
            @if($preview)
                @php
                    $totalItems = ($preview['movies'] ?? collect())->count() + ($preview['shows'] ?? collect())->count() + ($preview['episodes'] ?? collect())->count();
                @endphp
                @if($totalItems > 0)
                    <div class="mb-8 space-y-6">
                        {{-- Movies Preview --}}
                        @if(($preview['movies'] ?? collect())->count() > 0)
                            <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg overflow-hidden">
                                <div class="border-b border-theme-border-primary bg-theme-bg-tertiary px-4 py-4 sm:px-6">
                                    <h3 class="text-base font-semibold text-theme-text-primary">Movies ({{ $preview['movies']->count() }} preview)</h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-theme-bg-tertiary">
                                            <tr class="text-xs font-semibold text-theme-text-tertiary uppercase tracking-wide">
                                                <th class="px-4 py-3 text-left">Title</th>
                                                <th class="px-4 py-3 text-left">Director</th>
                                                <th class="px-4 py-3 text-left">Year</th>
                                                <th class="px-4 py-3 text-left">Rating</th>
                                                <th class="px-4 py-3 text-left">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-theme-border-primary">
                                            @foreach($preview['movies'] as $movie)
                                                <tr class="hover:bg-theme-bg-hover text-sm">
                                                    <td class="px-4 py-3 text-theme-text-primary font-medium">{{ Str::limit($movie['title'], 35) }}</td>
                                                    <td class="px-4 py-3 text-theme-text-secondary">{{ $movie['director'] ? Str::limit($movie['director'], 25) : '—' }}</td>
                                                    <td class="px-4 py-3 text-theme-text-secondary">{{ $movie['year'] ?? '—' }}</td>
                                                    <td class="px-4 py-3 text-theme-text-secondary">{{ $movie['rating'] ? $movie['rating'] . '/10' : '—' }}</td>
                                                    <td class="px-4 py-3">
                                                        @php
                                                            $statusValue = $movie['status'] instanceof \App\Enums\WatchingStatus ? $movie['status']->value : $movie['status'];
                                                        @endphp
                                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold
                                                            @switch($statusValue)
                                                                @case('watched') bg-theme-status-watched-bg text-theme-status-watched @break
                                                                @case('watching') bg-theme-status-watching-bg text-theme-status-watching @break
                                                                @default bg-theme-status-watchlist-bg text-theme-status-watchlist @break
                                                            @endswitch
                                                        ">
                                                            {{ ucfirst($statusValue) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Shows Preview --}}
                        @if(($preview['shows'] ?? collect())->count() > 0)
                            <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg overflow-hidden">
                                <div class="border-b border-theme-border-primary bg-theme-bg-tertiary px-4 py-4 sm:px-6">
                                    <h3 class="text-base font-semibold text-theme-text-primary">Shows ({{ $preview['shows']->count() }} preview)</h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-theme-bg-tertiary">
                                            <tr class="text-xs font-semibold text-theme-text-tertiary uppercase tracking-wide">
                                                <th class="px-4 py-3 text-left">Title</th>
                                                <th class="px-4 py-3 text-left">Year</th>
                                                <th class="px-4 py-3 text-left">Rating</th>
                                                <th class="px-4 py-3 text-left">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-theme-border-primary">
                                            @foreach($preview['shows'] as $show)
                                                <tr class="hover:bg-theme-bg-hover text-sm">
                                                    <td class="px-4 py-3 text-theme-text-primary font-medium">{{ Str::limit($show['title'], 35) }}</td>
                                                    <td class="px-4 py-3 text-theme-text-secondary">{{ $show['year'] ?? '—' }}</td>
                                                    <td class="px-4 py-3 text-theme-text-secondary">{{ $show['rating'] ? $show['rating'] . '/10' : '—' }}</td>
                                                    <td class="px-4 py-3">
                                                        @php
                                                            $statusValue = $show['status'] instanceof \App\Enums\WatchingStatus ? $show['status']->value : $show['status'];
                                                        @endphp
                                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold
                                                            @switch($statusValue)
                                                                @case('watched') bg-theme-status-watched-bg text-theme-status-watched @break
                                                                @case('watching') bg-theme-status-watching-bg text-theme-status-watching @break
                                                                @default bg-theme-status-watchlist-bg text-theme-status-watchlist @break
                                                            @endswitch
                                                        ">
                                                            {{ ucfirst($statusValue) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Episodes Preview --}}
                        @if(($preview['episodes'] ?? collect())->count() > 0)
                            <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg overflow-hidden">
                                <div class="border-b border-theme-border-primary bg-theme-bg-tertiary px-4 py-4 sm:px-6">
                                    <h3 class="text-base font-semibold text-theme-text-primary">Episodes ({{ $preview['episodes']->count() }} preview)</h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-theme-bg-tertiary">
                                            <tr class="text-xs font-semibold text-theme-text-tertiary uppercase tracking-wide">
                                                <th class="px-4 py-3 text-left">Show</th>
                                                <th class="px-4 py-3 text-left">Season/Episode</th>
                                                <th class="px-4 py-3 text-left">Rating</th>
                                                <th class="px-4 py-3 text-left">Air Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-theme-border-primary">
                                            @foreach($preview['episodes'] as $episode)
                                                <tr class="hover:bg-theme-bg-hover text-sm">
                                                    <td class="px-4 py-3 text-theme-text-primary font-medium">{{ Str::limit($episode['show_name'], 35) }}</td>
                                                    <td class="px-4 py-3 text-theme-text-secondary">S{{ str_pad($episode['season_number'], 2, '0', STR_PAD_LEFT) }}E{{ str_pad($episode['episode_number'], 2, '0', STR_PAD_LEFT) }}</td>
                                                    <td class="px-4 py-3 text-theme-text-secondary">{{ $episode['rating'] ? $episode['rating'] . '/10' : '—' }}</td>
                                                    <td class="px-4 py-3 text-theme-text-secondary">{{ $episode['release_date'] ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            @endif

            {{-- Import Result --}}
            @if($importResult)
                <div class="space-y-6">
                    <div class="grid grid-cols-3 gap-3 sm:gap-4">
                        <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-success-bg p-4 sm:p-6">
                            <div class="text-xs font-semibold text-theme-success-text uppercase tracking-wide">Imported</div>
                            <div class="mt-2 text-2xl sm:text-3xl font-bold text-theme-success">{{ $importResult['imported'] }}</div>
                        </div>
                        <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-warning-bg p-4 sm:p-6">
                            <div class="text-xs font-semibold text-theme-warning-text uppercase tracking-wide">Skipped</div>
                            <div class="mt-2 text-2xl sm:text-3xl font-bold text-theme-warning">{{ $importResult['skipped'] }}</div>
                        </div>
                        <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-danger-bg p-4 sm:p-6">
                            <div class="text-xs font-semibold text-theme-danger-text uppercase tracking-wide">Errors</div>
                            <div class="mt-2 text-2xl sm:text-3xl font-bold text-theme-danger">{{ count($importResult['errors']) }}</div>
                        </div>
                    </div>

                    @if(!empty($importResult['errors']))
                        <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-danger-bg p-4 sm:p-6">
                            <h3 class="font-semibold text-theme-danger-text mb-3">Errors</h3>
                            <ul class="space-y-2 text-sm text-theme-danger-text">
                                @foreach(array_slice($importResult['errors'], 0, 5) as $error)
                                    <li class="flex">
                                        <span class="mr-3 flex-shrink-0">&bull;</span>
                                        <span>{{ $error }}</span>
                                    </li>
                                @endforeach
                                @if(count($importResult['errors']) > 5)
                                    <li class="font-semibold italic">... and {{ count($importResult['errors']) - 5 }} more errors</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <a href="{{ route('movies.index') }}" class="inline-flex items-center justify-center rounded-md btn-secondary px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary">
                            View Movies
                        </a>
                        <button wire:click="resetForm" type="button" class="inline-flex items-center justify-center rounded-md btn-primary px-4 py-2 text-sm font-semibold shadow-sm">
                            Import More
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
