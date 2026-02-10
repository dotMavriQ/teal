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
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary" aria-current="page">Import</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-theme-text-primary">
                Import Anime
            </h1>
        </div>
    </header>

    <main class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(!$importResult)
                {{-- Import Mode Tabs --}}
                <div class="mb-6 flex rounded-lg bg-theme-card-bg ring-1 ring-theme-border-primary overflow-hidden">
                    <button
                        wire:click="setImportMode('username')"
                        class="flex-1 px-4 py-3 text-sm font-medium text-center transition-colors {{ $importMode === 'username' ? 'bg-theme-accent-primary text-white' : 'text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                    >
                        Fetch by Username
                    </button>
                    <button
                        wire:click="setImportMode('xml')"
                        class="flex-1 px-4 py-3 text-sm font-medium text-center transition-colors {{ $importMode === 'xml' ? 'bg-theme-accent-primary text-white' : 'text-theme-text-secondary hover:bg-theme-bg-hover' }}"
                    >
                        Upload XML Export
                    </button>
                </div>

                @if($importMode === 'username')
                    {{-- Username Fetch --}}
                    <div class="mb-8 rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg p-4 sm:p-6">
                        <h2 class="text-base font-semibold text-theme-text-primary mb-2">Fetch from MyAnimeList</h2>
                        <p class="text-sm text-theme-text-secondary mb-4">Enter your MAL username to fetch your entire anime list directly.</p>

                        <div class="flex gap-3">
                            <input
                                wire:model="malUsername"
                                type="text"
                                placeholder="MAL username"
                                class="flex-1 rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-accent-primary sm:text-sm sm:leading-6"
                                {{ $importing ? 'disabled' : '' }}
                            >
                            <button
                                wire:click="fetchFromMal"
                                wire:loading.attr="disabled"
                                wire:target="fetchFromMal"
                                class="inline-flex items-center rounded-md btn-primary px-4 py-2 text-sm font-semibold shadow-sm disabled:opacity-50"
                                {{ $importing ? 'disabled' : '' }}
                            >
                                <span wire:loading.remove wire:target="fetchFromMal">Fetch</span>
                                <span wire:loading wire:target="fetchFromMal" class="flex items-center">
                                    <svg class="animate-spin mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Fetching...
                                </span>
                            </button>
                        </div>
                        @error('malUsername')
                            <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    {{-- XML Upload --}}
                    <div class="mb-8 rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg p-4 sm:p-6">
                        <h2 class="text-base font-semibold text-theme-text-primary mb-2">Upload MAL XML Export</h2>
                        <div class="space-y-4 text-sm text-theme-text-secondary mb-4">
                            <div>
                                <h3 class="font-semibold text-theme-text-primary mb-2">How to export from MAL:</h3>
                                <ol class="list-decimal list-inside space-y-1 text-theme-text-secondary">
                                    <li>Go to myanimelist.net/panel.php?go=export</li>
                                    <li>Select "Anime List" and click "Export"</li>
                                    <li>Download and extract the XML file</li>
                                </ol>
                            </div>
                        </div>

                        <div class="border-2 border-dashed border-theme-border-secondary rounded-lg p-6">
                            <label for="file" class="block text-sm font-semibold text-theme-text-primary mb-2">
                                Choose XML File
                            </label>
                            <input
                                type="file"
                                id="file"
                                wire:model="file"
                                accept=".xml"
                                class="block w-full text-sm text-theme-text-secondary file:rounded-md file:border-0 file:btn-primary file:px-4 file:py-2 file:text-sm file:font-semibold"
                                {{ $importing ? 'disabled' : '' }}
                            >
                            @error('file')
                                <p class="mt-2 text-sm text-theme-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- Options & Import Button (shared) --}}
                @if($preview)
                    <div class="mb-8 rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-theme-text-primary">Preview ({{ $totalEntries }} total entries)</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-theme-bg-tertiary">
                                    <tr class="text-xs font-semibold text-theme-text-tertiary uppercase tracking-wide">
                                        <th class="px-4 py-3 text-left">Title</th>
                                        <th class="px-4 py-3 text-left">Type</th>
                                        <th class="px-4 py-3 text-left">Rating</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-theme-border-primary">
                                    @foreach($preview as $entry)
                                        <tr class="hover:bg-theme-bg-hover text-sm">
                                            <td class="px-4 py-3 text-theme-text-primary font-medium">{{ Str::limit($entry['title'], 40) }}</td>
                                            <td class="px-4 py-3 text-theme-text-secondary">{{ $entry['media_type'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-theme-text-secondary">{{ $entry['rating'] ? $entry['rating'] . '/10' : '—' }}</td>
                                            <td class="px-4 py-3">
                                                @php
                                                    $statusValue = $entry['status'] instanceof \App\Enums\WatchingStatus ? $entry['status']->value : $entry['status'];
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
                        @if($totalEntries > 5)
                            <p class="mt-2 text-xs text-theme-text-muted">Showing 5 of {{ $totalEntries }} entries.</p>
                        @endif

                        <div class="mt-4 flex items-center">
                            <input type="checkbox" id="skipDuplicates" wire:model="skipDuplicates" class="h-4 w-4 rounded" {{ $importing ? 'disabled' : '' }}>
                            <label for="skipDuplicates" class="ml-3 text-sm text-theme-text-secondary">
                                Skip duplicates (matched by MAL ID)
                            </label>
                        </div>

                        <div class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <a href="{{ route('anime.index') }}" class="inline-flex items-center justify-center rounded-md btn-secondary px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary">
                                Cancel
                            </a>
                            <button
                                wire:click="import"
                                wire:loading.attr="disabled"
                                wire:target="import"
                                class="inline-flex items-center justify-center rounded-md btn-primary px-4 py-2 text-sm font-semibold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="import">Import {{ $totalEntries }} Anime</span>
                                <span wire:loading wire:target="import" class="flex items-center">
                                    <svg class="animate-spin mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Importing...
                                </span>
                            </button>
                        </div>
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
                        <a href="{{ route('anime.index') }}" class="inline-flex items-center justify-center rounded-md btn-secondary px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary">
                            View Anime
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
