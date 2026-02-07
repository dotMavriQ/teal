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
                            <a href="{{ route('movies.settings') }}" class="ml-4 text-sm font-medium text-theme-text-secondary hover:text-theme-text-primary">Settings</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-tertiary" aria-current="page">Metadata Enrichment</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="mt-2">
                <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">
                    Metadata Enrichment
                </h1>
                <p class="mt-1 text-sm text-theme-text-secondary">Fetch and apply missing movie metadata and posters from TMDB.</p>
            </div>
        </div>
    </header>

    <main class="py-6 sm:py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="rounded-md bg-theme-success-bg p-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-theme-success" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-theme-success">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="rounded-md bg-theme-danger-bg p-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-theme-danger" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-theme-danger">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- API Key Warning --}}
            @if(!app(\App\Services\TmdbService::class)->isConfigured())
                <div class="rounded-md bg-theme-warning-bg p-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-theme-warning" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-theme-warning-text">TMDB API Key Required</h3>
                            <p class="mt-1 text-sm text-theme-text-secondary">
                                Add your TMDB credentials to your <code class="rounded bg-theme-bg-tertiary px-1.5 py-0.5 text-xs font-mono">.env</code> file:<br>
                                <code class="rounded bg-theme-bg-tertiary px-1.5 py-0.5 text-xs font-mono">TMDB_API_KEY=your_key</code> and
                                <code class="rounded bg-theme-bg-tertiary px-1.5 py-0.5 text-xs font-mono">TMDB_ACCESS_TOKEN=your_token</code>.<br>
                                Get both free at <span class="font-medium">themoviedb.org/settings/api</span>.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Source Priority Card --}}
            <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium leading-6 text-theme-text-primary">Source Priority</h2>
                    <p class="mt-1 text-sm text-theme-text-secondary">When both your library and TMDB have a value for a field, which source should take precedence?</p>

                    <div class="mt-4 space-y-2">
                        @foreach($sourcePriority as $index => $source)
                            <div class="flex items-center justify-between p-3 bg-theme-bg-tertiary rounded-lg ring-1 ring-theme-border-primary">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-theme-status-watchlist-bg text-theme-status-watchlist text-sm font-medium">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="font-medium text-theme-text-primary">{{ $this->getSourceLabel($source) }}</span>
                                    @if($index === 0)
                                        <span class="inline-flex items-center rounded-full bg-theme-success-bg px-2 py-0.5 text-xs font-medium text-theme-success">
                                            Highest Priority
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1">
                                    <button
                                        wire:click="moveSourceUp('{{ $source }}')"
                                        type="button"
                                        class="p-1.5 text-theme-text-muted hover:text-theme-text-primary disabled:opacity-30 disabled:cursor-not-allowed rounded hover:bg-theme-bg-hover"
                                        @if($index === 0) disabled @endif
                                        title="Move up"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="moveSourceDown('{{ $source }}')"
                                        type="button"
                                        class="p-1.5 text-theme-text-muted hover:text-theme-text-primary disabled:opacity-30 disabled:cursor-not-allowed rounded hover:bg-theme-bg-hover"
                                        @if($index === count($sourcePriority) - 1) disabled @endif
                                        title="Move down"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 p-3 bg-theme-status-watchlist-bg rounded-lg">
                        <p class="text-sm text-theme-text-secondary">
                            @if($sourcePriority[0] === 'current')
                                <strong class="text-theme-text-primary">Current mode:</strong> TMDB will only fill in <em>empty</em> fields. Existing values in your library will be preserved.
                            @else
                                <strong class="text-theme-text-primary">Current mode:</strong> TMDB values will <em>overwrite</em> existing values in your library when available.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Job Status Panel --}}
            @if($jobStatus)
                <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg" wire:poll.5s="refreshJobStatus">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-medium leading-6 text-theme-text-primary">Background Job Status</h2>
                                <p class="mt-1 text-sm text-theme-text-secondary">Metadata enrichment in progress...</p>
                            </div>
                            @if($jobStatus['status'] === 'running')
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin h-6 w-6 text-theme-status-watchlist" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-theme-status-watchlist">Running</span>
                                </div>
                            @elseif($jobStatus['status'] === 'completed')
                                <div class="flex items-center gap-2">
                                    <svg class="h-6 w-6 text-theme-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm font-medium text-theme-success">Completed</span>
                                </div>
                            @endif
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-theme-text-secondary">Progress</span>
                                <span class="text-sm font-medium text-theme-text-primary">
                                    {{ $jobStatus['fetched'] ?? 0 }}/{{ $jobStatus['total'] }}
                                </span>
                            </div>
                            <div class="w-full bg-theme-bg-tertiary rounded-full h-2">
                                <div
                                    class="bg-theme-status-watchlist h-2 rounded-full transition-all duration-500"
                                    style="width: {{ $jobStatus['total'] > 0 ? (($jobStatus['fetched'] ?? 0) / $jobStatus['total'] * 100) : 0 }}%"
                                ></div>
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="mt-4 grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-theme-status-watchlist">{{ $jobStatus['fetched'] ?? 0 }}</div>
                                <div class="text-xs text-theme-text-muted mt-1">Fetched</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-theme-success">{{ $jobStatus['applied'] ?? 0 }}</div>
                                <div class="text-xs text-theme-text-muted mt-1">Applied</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-theme-text-secondary">{{ $jobStatus['total'] }}</div>
                                <div class="text-xs text-theme-text-muted mt-1">Total</div>
                            </div>
                        </div>

                        @if($jobStatus['status'] === 'completed')
                            <div class="mt-4 flex gap-2">
                                <button
                                    wire:click="clearJobStatus"
                                    type="button"
                                    class="inline-flex items-center rounded-md btn-secondary px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary"
                                >
                                    Clear Status
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Scan & Fetch Card --}}
            <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium leading-6 text-theme-text-primary">Scan & Fetch</h2>
                    <p class="mt-1 text-sm text-theme-text-secondary">Scan your library for movies with missing metadata, then fetch from TMDB.</p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            wire:click="scanLibrary"
                            wire:loading.attr="disabled"
                            wire:target="scanLibrary"
                            type="button"
                            class="inline-flex items-center rounded-md btn-primary px-4 py-2 text-sm font-semibold shadow-sm disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="scanLibrary">
                                <svg class="-ml-0.5 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                                Scan Library
                            </span>
                            <span wire:loading wire:target="scanLibrary" class="flex items-center">
                                <svg class="animate-spin -ml-0.5 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Scanning...
                            </span>
                        </button>

                        @if($hasScanned && !empty($moviesNeedingEnrichment))
                            @php $needsFetch = $this->getMoviesWithMissingCount(); @endphp
                            @if($needsFetch > 0 && !$this->isJobRunning())
                                <button
                                    wire:click="startBackgroundFetch"
                                    wire:loading.attr="disabled"
                                    wire:target="startBackgroundFetch"
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-theme-status-watchlist px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg wire:loading.remove wire:target="startBackgroundFetch" class="-ml-0.5 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    <svg wire:loading wire:target="startBackgroundFetch" class="animate-spin -ml-0.5 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="startBackgroundFetch">Fetch Missing Metadata ({{ min($needsFetch, $batchLimit) }}{{ $needsFetch > $batchLimit ? ' of ' . $needsFetch : '' }})</span>
                                    <span wire:loading wire:target="startBackgroundFetch">Fetching metadata... please wait</span>
                                </button>
                            @elseif($this->isJobRunning())
                                <button type="button" disabled class="inline-flex items-center rounded-md bg-theme-status-watchlist px-4 py-2 text-sm font-semibold text-white shadow-sm opacity-50 cursor-not-allowed">
                                    <svg class="animate-spin -ml-0.5 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Background fetch running...
                                </button>
                            @else
                                <span class="inline-flex items-center text-sm text-theme-success">
                                    <svg class="mr-1.5 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    All metadata up to date
                                </span>
                            @endif
                        @endif
                    </div>

                    @if($hasScanned)
                        <div class="mt-4 flex flex-wrap gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-theme-warning-bg text-theme-warning font-medium">
                                    {{ $this->getMoviesWithMissingCount() }}
                                </span>
                                <span class="text-theme-text-secondary">Movies need metadata</span>
                            </div>
                            @if(!empty($fetchedData))
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-theme-success-bg text-theme-success font-medium">
                                        {{ $this->getFetchedCount() }}
                                    </span>
                                    <span class="text-theme-text-secondary">Fetched from TMDB</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Results Table --}}
            @if($hasScanned && !empty($moviesNeedingEnrichment))
                <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-theme-border-primary">
                        <h2 class="text-lg font-medium leading-6 text-theme-text-primary">Movies</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-theme-border-primary">
                            <thead class="bg-theme-bg-tertiary">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-theme-text-primary sm:pl-6">Movie</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-theme-text-primary">Status</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-theme-text-primary">Missing Fields</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-theme-border-primary">
                                @foreach($moviesNeedingEnrichment as $movie)
                                    @if($movie['has_missing'])
                                    <tr wire:key="movie-row-{{ $movie['id'] }}">
                                        <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                            <div class="font-medium text-theme-text-primary">{{ $movie['title'] }}</div>
                                            <div class="text-theme-text-secondary">{{ $movie['director'] ?? 'Unknown director' }}</div>
                                            @if($movie['imdb_id'])
                                                <div class="text-theme-text-muted text-xs font-mono">{{ $movie['imdb_id'] }}</div>
                                            @elseif($movie['year'])
                                                <div class="text-theme-text-muted text-xs">{{ $movie['year'] }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            @if($movie['is_episode'] ?? false)
                                                <span class="inline-flex items-center rounded-full bg-theme-bg-tertiary px-2 py-1 text-xs font-medium text-theme-text-muted">
                                                    Skipped (episode)
                                                </span>
                                            @elseif(isset($fetchedData[$movie['id']]))
                                                <span class="inline-flex items-center rounded-full bg-theme-success-bg px-2 py-1 text-xs font-medium text-theme-success">
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    Fetched
                                                </span>
                                            @elseif($movie['metadata_fetched_at'] ?? false)
                                                <span class="inline-flex items-center rounded-full bg-theme-status-watching-bg px-2 py-1 text-xs font-medium text-theme-status-watching">
                                                    Retryable
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-theme-warning-bg px-2 py-1 text-xs font-medium text-theme-warning">
                                                    Needs data
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 text-sm text-theme-text-secondary">
                                            @if(!empty($movie['missing']))
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($movie['missing'] as $field)
                                                        <span class="inline-flex items-center rounded bg-theme-bg-tertiary px-1.5 py-0.5 text-xs text-theme-text-muted">
                                                            {{ str_replace('_', ' ', $field) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-theme-text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button
                                                wire:click="startReview({{ $movie['id'] }})"
                                                type="button"
                                                class="text-theme-status-watchlist hover:opacity-80"
                                            >
                                                Review
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($hasScanned)
                <div class="rounded-lg ring-1 ring-theme-border-primary bg-theme-card-bg">
                    <div class="px-4 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-theme-text-primary">All metadata is complete</h3>
                        <p class="mt-1 text-sm text-theme-text-secondary">All your movies have complete metadata.</p>
                    </div>
                </div>
            @endif
        </div>
    </main>

    {{-- Review Modal --}}
    @if($showReviewModal && $reviewingMovie)
        <div
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="review-modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div wire:click="closeReviewModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="relative transform overflow-hidden rounded-lg bg-theme-card-bg px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
                    <div>
                        <h3 class="text-lg font-semibold leading-6 text-theme-text-primary" id="review-modal-title">
                            Review: "{{ $reviewingMovie['title'] }}"
                        </h3>
                        <p class="mt-1 text-sm text-theme-text-secondary">{{ $reviewingMovie['director'] ?? 'Unknown director' }}{{ $reviewingMovie['year'] ? ' (' . $reviewingMovie['year'] . ')' : '' }}</p>

                        @if(!$reviewingMetadata && !isset($fetchedData[$reviewingMovieId]))
                            <div class="mt-6">
                                <button
                                    wire:click="fetchSingleMovie({{ $reviewingMovieId }})"
                                    wire:loading.attr="disabled"
                                    wire:target="fetchSingleMovie"
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-theme-status-watchlist px-3 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90"
                                >
                                    <span wire:loading.remove wire:target="fetchSingleMovie">Fetch from TMDB</span>
                                    <span wire:loading wire:target="fetchSingleMovie" class="flex items-center">
                                        <svg class="animate-spin mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Fetching...
                                    </span>
                                </button>
                            </div>
                        @elseif(!$reviewingMetadata)
                            <div class="mt-6 rounded-md bg-theme-warning-bg p-4">
                                <p class="text-sm text-theme-warning">Could not fetch metadata from TMDB for this movie.</p>
                            </div>
                        @else
                            <div class="mt-6 overflow-x-auto">
                                <table class="min-w-full divide-y divide-theme-border-primary">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-theme-text-primary">Field</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-theme-text-primary">Current</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-theme-text-primary">TMDB</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-theme-border-primary">
                                        @php
                                            $fields = [
                                                'description' => 'Description',
                                                'poster_url' => 'Poster',
                                                'runtime_minutes' => 'Runtime',
                                                'release_date' => 'Release Date',
                                                'genres' => 'Genres',
                                                'director' => 'Director',
                                            ];
                                        @endphp
                                        @foreach($fields as $field => $label)
                                            @php
                                                $currentValue = $reviewingMovie['current'][$field] ?? null;
                                                $newValue = $reviewingMetadata[$field] ?? null;
                                                $hasConflict = !empty($currentValue) && !empty($newValue) && $currentValue != $newValue;
                                            @endphp
                                            <tr class="{{ $hasConflict ? 'bg-theme-warning-bg' : '' }}">
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                                    <label class="flex items-center">
                                                        <input
                                                            type="checkbox"
                                                            wire:model="selectedFields"
                                                            value="{{ $field }}"
                                                            class="h-4 w-4 rounded border-theme-border-primary text-theme-status-watchlist focus:ring-theme-status-watchlist"
                                                            @if(empty($newValue)) disabled @endif
                                                        >
                                                        <span class="ml-2 font-medium text-theme-text-primary">{{ $label }}</span>
                                                        @if($hasConflict)
                                                            <span class="ml-2 inline-flex items-center rounded-full bg-theme-warning-bg px-1.5 py-0.5 text-xs font-medium text-theme-warning">
                                                                conflict
                                                            </span>
                                                        @endif
                                                    </label>
                                                </td>
                                                <td class="px-3 py-4 text-sm text-theme-text-secondary max-w-[200px]">
                                                    @if($field === 'poster_url' && $currentValue)
                                                        <img src="{{ $currentValue }}" alt="Current poster" class="h-16 w-auto rounded">
                                                    @elseif($field === 'description' && $currentValue)
                                                        <div class="truncate" title="{{ $currentValue }}">{{ $currentValue }}</div>
                                                    @elseif($field === 'runtime_minutes' && $currentValue)
                                                        {{ $currentValue }} min
                                                    @else
                                                        {{ $currentValue ?: '(empty)' }}
                                                    @endif
                                                </td>
                                                <td class="px-3 py-4 text-sm max-w-[200px]">
                                                    @if($newValue)
                                                        @if($field === 'poster_url')
                                                            <img src="{{ $newValue }}" alt="TMDB poster" class="h-16 w-auto rounded">
                                                        @elseif($field === 'description')
                                                            <div class="truncate text-theme-success" title="{{ $newValue }}">{{ $newValue }}</div>
                                                        @elseif($field === 'runtime_minutes')
                                                            <span class="text-theme-success">{{ $newValue }} min</span>
                                                        @else
                                                            <span class="text-theme-success">{{ $newValue }}</span>
                                                        @endif
                                                    @else
                                                        <span class="text-theme-text-muted">(not available)</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse gap-3">
                        @if($reviewingMetadata && count($selectedFields) > 0)
                            <button
                                wire:click="applyMetadata"
                                type="button"
                                class="inline-flex w-full justify-center rounded-md btn-primary px-3 py-2 text-sm font-semibold shadow-sm sm:w-auto"
                            >
                                Apply ({{ count($selectedFields) }})
                            </button>
                        @endif
                        <button
                            wire:click="skipMovie"
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md btn-secondary px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-theme-border-primary sm:mt-0 sm:w-auto"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
