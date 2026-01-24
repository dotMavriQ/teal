<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
                            </svg>
                            <span class="sr-only">Home</span>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('books.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Books</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('books.settings') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Settings</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500" aria-current="page">Metadata Enrichment</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="mt-2">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    Metadata Enrichment
                </h1>
                <p class="mt-1 text-sm text-gray-500">Fetch and apply missing book metadata from OpenLibrary.</p>
            </div>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="rounded-md bg-green-50 p-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="rounded-md bg-red-50 p-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Source Priority Card --}}
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium leading-6 text-gray-900">Source Priority</h2>
                    <p class="mt-1 text-sm text-gray-500">When both your library and OpenLibrary have a value for a field, which source should take precedence?</p>

                    <div class="mt-4 space-y-2">
                        @foreach($sourcePriority as $index => $source)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-sm font-medium">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $this->getSourceLabel($source) }}</span>
                                    @if($index === 0)
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                            Highest Priority
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1">
                                    <button
                                        wire:click="moveSourceUp('{{ $source }}')"
                                        type="button"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed rounded hover:bg-gray-200"
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
                                        class="p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed rounded hover:bg-gray-200"
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

                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-700">
                            @if($sourcePriority[0] === 'current')
                                <strong>Current mode:</strong> OpenLibrary will only fill in <em>empty</em> fields. Existing values in your library will be preserved.
                            @else
                                <strong>Current mode:</strong> OpenLibrary values will <em>overwrite</em> existing values in your library when available.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Job Status Panel --}}
            @if($jobStatus)
                <div class="bg-white shadow rounded-lg" wire:poll.5s="refreshJobStatus">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-medium leading-6 text-gray-900">Background Job Status</h2>
                                <p class="mt-1 text-sm text-gray-500">Metadata enrichment in progress...</p>
                            </div>
                            @if($jobStatus['status'] === 'running')
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-600">Running</span>
                                </div>
                            @elseif($jobStatus['status'] === 'completed')
                                <div class="flex items-center gap-2">
                                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm font-medium text-green-600">Completed</span>
                                </div>
                            @endif
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Progress</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $jobStatus['fetched'] ?? 0 }}/{{ $jobStatus['total'] }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                                    style="width: {{ $jobStatus['total'] > 0 ? (($jobStatus['fetched'] ?? 0) / $jobStatus['total'] * 100) : 0 }}%"
                                ></div>
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="mt-4 grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $jobStatus['fetched'] ?? 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">Fetched</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $jobStatus['applied'] ?? 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">Applied</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-600">{{ $jobStatus['total'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">Total</div>
                            </div>
                        </div>

                        {{-- Clear Status Button (only show if completed) --}}
                        @if($jobStatus['status'] === 'completed')
                            <div class="mt-4 flex gap-2">
                                <button
                                    wire:click="clearJobStatus"
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500"
                                >
                                    Clear Status
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Scan & Fetch Card --}}
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium leading-6 text-gray-900">Scan & Fetch</h2>
                    <p class="mt-1 text-sm text-gray-500">Scan your library for books with ISBNs, then fetch metadata from OpenLibrary in the background.</p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            wire:click="scanLibrary"
                            wire:loading.attr="disabled"
                            wire:target="scanLibrary"
                            type="button"
                            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 disabled:opacity-50"
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

                        @if($hasScanned && !empty($booksNeedingEnrichment))
                            @php $needsFetch = $this->getBooksWithMissingCount(); @endphp
                            @if($needsFetch > 0 && !$this->isJobRunning())
                                <button
                                    wire:click="startBackgroundFetch"
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 disabled:opacity-50"
                                >
                                    <svg class="-ml-0.5 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    Start Background Fetch ({{ min($needsFetch, $batchLimit) }}{{ $needsFetch > $batchLimit ? ' of ' . $needsFetch : '' }})
                                </button>
                            @elseif($this->isJobRunning())
                                <button type="button" disabled class="inline-flex items-center rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm opacity-50 cursor-not-allowed">
                                    <svg class="animate-spin -ml-0.5 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Background fetch running...
                                </button>
                            @else
                                <span class="inline-flex items-center text-sm text-green-600">
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
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-medium">
                                    {{ count($booksNeedingEnrichment) }}
                                </span>
                                <span class="text-gray-600">Books with ISBN</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-700 font-medium">
                                    {{ $this->getBooksWithMissingCount() }}
                                </span>
                                <span class="text-gray-600">Missing metadata</span>
                            </div>
                            @if(!empty($fetchedData))
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-700 font-medium">
                                        {{ $this->getFetchedCount() }}
                                    </span>
                                    <span class="text-gray-600">Fetched from OpenLibrary</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Results Table --}}
            @if($hasScanned && !empty($booksNeedingEnrichment))
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h2 class="text-lg font-medium leading-6 text-gray-900">Books</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Book</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Missing Fields</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($booksNeedingEnrichment as $book)
                                    <tr wire:key="book-row-{{ $book['id'] }}">
                                        <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                            <div class="font-medium text-gray-900">{{ $book['title'] }}</div>
                                            <div class="text-gray-500">{{ $book['author'] }}</div>
                                            <div class="text-gray-400 text-xs font-mono">{{ $book['isbn'] }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            @if(isset($fetchedData[$book['id']]))
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    Fetched
                                                </span>
                                            @elseif($book['has_missing'])
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">
                                                    Needs data
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                                    Complete
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            @if(!empty($book['missing']))
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($book['missing'] as $field)
                                                        <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">
                                                            {{ str_replace('_', ' ', $field) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button
                                                wire:click="startReview({{ $book['id'] }})"
                                                type="button"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Review
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($hasScanned)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No books with ISBN found</h3>
                        <p class="mt-1 text-sm text-gray-500">Import books with ISBNs to use metadata enrichment.</p>
                    </div>
                </div>
            @endif
        </div>
    </main>

    {{-- Review Modal --}}
    @if($showReviewModal && $reviewingBook)
        <div
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="review-modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div wire:click="closeReviewModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
                    <div>
                        <h3 class="text-lg font-semibold leading-6 text-gray-900" id="review-modal-title">
                            Review: "{{ $reviewingBook['title'] }}"
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $reviewingBook['author'] }}</p>

                        @if(!$reviewingMetadata && !isset($fetchedData[$reviewingBookId]))
                            <div class="mt-6">
                                <button
                                    wire:click="fetchSingleBook({{ $reviewingBookId }})"
                                    wire:loading.attr="disabled"
                                    wire:target="fetchSingleBook"
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500"
                                >
                                    <span wire:loading.remove wire:target="fetchSingleBook">Fetch from OpenLibrary</span>
                                    <span wire:loading wire:target="fetchSingleBook" class="flex items-center">
                                        <svg class="animate-spin mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Fetching...
                                    </span>
                                </button>
                            </div>
                        @elseif(!$reviewingMetadata)
                            <div class="mt-6 rounded-md bg-yellow-50 p-4">
                                <p class="text-sm text-yellow-700">Could not fetch metadata from OpenLibrary for this ISBN.</p>
                            </div>
                        @else
                            <div class="mt-6 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Field</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Current</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">OpenLibrary</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @php
                                            $fields = [
                                                'description' => 'Description',
                                                'publisher' => 'Publisher',
                                                'page_count' => 'Pages',
                                                'published_date' => 'Published',
                                            ];
                                        @endphp
                                        @foreach($fields as $field => $label)
                                            @php
                                                $currentValue = $reviewingBook['current'][$field] ?? null;
                                                $newValue = $reviewingMetadata[$field] ?? null;
                                                $hasConflict = !empty($currentValue) && !empty($newValue) && $currentValue != $newValue;
                                            @endphp
                                            <tr class="{{ $hasConflict ? 'bg-amber-50' : '' }}">
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                                    <label class="flex items-center">
                                                        <input
                                                            type="checkbox"
                                                            wire:model="selectedFields"
                                                            value="{{ $field }}"
                                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                                                            @if(empty($newValue)) disabled @endif
                                                        >
                                                        <span class="ml-2 font-medium text-gray-900">{{ $label }}</span>
                                                        @if($hasConflict)
                                                            <span class="ml-2 inline-flex items-center rounded-full bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-700">
                                                                conflict
                                                            </span>
                                                        @endif
                                                    </label>
                                                </td>
                                                <td class="px-3 py-4 text-sm text-gray-500 max-w-[200px]">
                                                    @if($field === 'description' && $currentValue)
                                                        <div class="truncate" title="{{ $currentValue }}">{{ $currentValue }}</div>
                                                    @else
                                                        {{ $currentValue ?: '(empty)' }}
                                                    @endif
                                                </td>
                                                <td class="px-3 py-4 text-sm max-w-[200px]">
                                                    @if($newValue)
                                                        @if($field === 'description')
                                                            <div class="truncate text-green-700" title="{{ $newValue }}">{{ $newValue }}</div>
                                                        @else
                                                            <span class="text-green-700">{{ $newValue }}</span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-400">(not available)</span>
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
                                class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:w-auto"
                            >
                                Apply ({{ count($selectedFields) }})
                            </button>
                        @endif
                        <button
                            wire:click="skipBook"
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
