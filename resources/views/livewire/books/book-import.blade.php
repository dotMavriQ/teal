<div class="py-6 sm:py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Format Selection (Mobile First) --}}
        @if(!$importResult)
            <div class="mb-8 grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4">
            {{-- CSV Format --}}
            <button
                        wire:click="$set('format', 'csv')"
                        type="button"
                        class="relative rounded-lg border-2 p-4 text-center transition-all {{ $format === 'csv' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                    >
                        <div class="flex flex-col items-center">
                            <svg class="mb-2 h-8 w-8 {{ $format === 'csv' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="text-sm font-semibold {{ $format === 'csv' ? 'text-blue-900' : 'text-gray-900' }}">CSV Format</h3>
                            <p class="mt-1 text-xs {{ $format === 'csv' ? 'text-blue-700' : 'text-gray-500' }}">GoodReads Export</p>
                        </div>
                        @if($format === 'csv')
                            <div class="absolute top-2 right-2">
                                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endif
                    </button>

                    {{-- JSON Format --}}
                    <button
                        wire:click="$set('format', 'json')"
                        type="button"
                        class="relative rounded-lg border-2 p-4 text-center transition-all {{ $format === 'json' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                    >
                        <div class="flex flex-col items-center">
                            <svg class="mb-2 h-8 w-8 {{ $format === 'json' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            <h3 class="text-sm font-semibold {{ $format === 'json' ? 'text-blue-900' : 'text-gray-900' }}">JSON Format</h3>
                            <p class="mt-1 text-xs {{ $format === 'json' ? 'text-blue-700' : 'text-gray-500' }}">Custom Format</p>
                        </div>
                        @if($format === 'json')
                            <div class="absolute top-2 right-2">
                                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endif
                    </button>
                </div>

                {{-- Format Specs --}}
                <div class="mb-8 rounded-lg border border-gray-200 bg-white p-4 sm:p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">
                        {{ $format === 'json' ? 'JSON Format Specifications' : 'CSV Format Specifications' }}
                    </h2>

                    @if($format === 'csv')
                        <div class="space-y-4 text-sm text-gray-600">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">Supported Columns:</h3>
                                <p class="text-xs font-mono bg-gray-50 p-2 rounded overflow-x-auto mb-2">
                                    Book Id, Title, Author, Additional Authors, ISBN, ISBN13, Publisher, Year Published, Original Publication Year, Number of Pages, My Rating, Exclusive Shelf, Date Started, Date Read, My Review
                                </p>
                                <p class="text-gray-700">Export your library from GoodReads as CSV. Required: Title. Optional: ISBN13, Author, etc.</p>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">How to export:</h3>
                                <ol class="list-decimal list-inside space-y-1 text-gray-700">
                                    <li>Visit your GoodReads shelf</li>
                                    <li>Click on settings icon (top right)</li>
                                    <li>Select "Export Library"</li>
                                    <li>Download the CSV file</li>
                                </ol>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">Duplicate Handling:</h3>
                                <p class="text-gray-700">Books are matched by ISBN13 → ISBN → Title+Author. Enable "Skip duplicates" to avoid importing books already in your library.</p>
                            </div>
                        </div>
                    @else
                        <div class="space-y-4 text-sm text-gray-600">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">Supported Fields:</h3>
                                <div class="grid grid-cols-2 gap-2 text-xs font-mono bg-gray-50 p-3 rounded">
                                    <div class="col-span-2"><strong>Required:</strong></div>
                                    <div>title</div>
                                    <div class="text-gray-500">(string)</div>
                                    <div class="col-span-2 mt-2"><strong>Metadata:</strong></div>
                                    <div>author, isbn, isbn13, asin</div>
                                    <div class="text-gray-500">num_pages, published_date</div>
                                    <div class="col-span-2 mt-2"><strong>Ratings:</strong></div>
                                    <div>rating (1-5), avg_rating</div>
                                    <div class="text-gray-500">num_ratings</div>
                                    <div class="col-span-2 mt-2"><strong>Dates:</strong></div>
                                    <div>date_started, date_read</div>
                                    <div class="text-gray-500">date_added, date_pub, date_pub__ed__</div>
                                    <div class="col-span-2 mt-2"><strong>Content:</strong></div>
                                    <div>review, notes, shelves</div>
                                    <div class="text-gray-500">comments, votes</div>
                                    <div class="col-span-2 mt-2"><strong>Other:</strong></div>
                                    <div>bookCover, owned</div>
                                    <div class="text-gray-500">(external URL or boolean)</div>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">JSON Structure:</h3>
                                <pre class="text-xs font-mono bg-gray-50 p-3 rounded overflow-x-auto"><code>[
  {
    "title": "Book Title",
    "author": "Author Name",
    "isbn13": "9780123456789",
    "rating": 5,
    "review": "Great book!",
    "bookCover": "https://example.com/cover.jpg"
  }
]</code></pre>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">Duplicate Handling:</h3>
                                <p class="text-gray-700">Books are matched by ISBN13 → ISBN → ASIN → Title+Author. Enable "Skip duplicates" to avoid importing books already in your library.</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- File Upload --}}
                <div class="mb-8 rounded-lg border-2 border-dashed border-gray-300 bg-white p-6 sm:p-8">
                    <form wire:submit="import" class="space-y-4">
                        <div>
                            <label for="file" class="block text-sm font-semibold text-gray-900 mb-2">
                                Choose {{ $format === 'json' ? 'JSON' : 'CSV' }} File
                            </label>
                            <input
                                type="file"
                                id="file"
                                wire:model="file"
                                accept="{{ $format === 'json' ? '.json' : '.csv,.txt' }}"
                                class="block w-full text-sm text-gray-500 file:rounded-md file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-700"
                                {{ $importing ? 'disabled' : '' }}
                            >
                            @error('file')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Options --}}
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="skipDuplicates"
                                wire:model="skipDuplicates"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600"
                                {{ $importing ? 'disabled' : '' }}
                            >
                            <label for="skipDuplicates" class="ml-3 text-sm text-gray-700">
                                Skip duplicate books
                            </label>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end pt-4">
                            <a
                                href="{{ route('books.index') }}"
                                class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                {{ !$file || $importing ? 'disabled' : '' }}
                                class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                @if($importing)
                                    <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Importing...
                                @else
                                    Import Books
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Preview --}}
            @if($preview && $preview->count() > 0)
                <div class="mb-8 rounded-lg border border-gray-200 bg-white overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-4 sm:px-6">
                        <h3 class="text-base font-semibold text-gray-900">Preview ({{ $preview->count() }} of total)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr class="text-xs font-semibold text-gray-900 uppercase tracking-wide">
                                    <th class="px-4 py-3 text-left">Title</th>
                                    <th class="px-4 py-3 text-left">Author</th>
                                    <th class="px-4 py-3 text-left">ISBN</th>
                                    <th class="px-4 py-3 text-left">Rating</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($preview as $book)
                                    <tr class="hover:bg-gray-50 text-sm">
                                        <td class="px-4 py-3 text-gray-900 font-medium">{{ Str::limit($book['title'], 30) }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $book['author'] ? Str::limit($book['author'], 25) : '—' }}</td>
                                        <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $book['isbn13'] ?? $book['isbn'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $book['rating'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold {{ match($book['status']) {
                                                'read' => 'bg-green-100 text-green-800',
                                                'reading' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            } }}">
                                                {{ ucfirst(str_replace('_', ' ', $book['status'])) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Import Result --}}
            @if($importResult)
                <div class="space-y-6">
                    @if(isset($importResult['async']) && $importResult['async'])
                        <div class="rounded-lg border-l-4 border-blue-500 bg-blue-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-800">{{ $importResult['message'] }}</p>
                                    <p class="mt-1 text-xs text-blue-700">You'll receive a notification when the import completes.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-3 gap-3 sm:gap-4">
                            <div class="rounded-lg border border-green-200 bg-green-50 p-4 sm:p-6">
                                <div class="text-xs font-semibold text-green-800 uppercase tracking-wide">Imported</div>
                                <div class="mt-2 text-2xl sm:text-3xl font-bold text-green-600">{{ $importResult['imported'] }}</div>
                            </div>
                            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 sm:p-6">
                                <div class="text-xs font-semibold text-yellow-800 uppercase tracking-wide">Skipped</div>
                                <div class="mt-2 text-2xl sm:text-3xl font-bold text-yellow-600">{{ $importResult['skipped'] }}</div>
                            </div>
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 sm:p-6">
                                <div class="text-xs font-semibold text-red-800 uppercase tracking-wide">Errors</div>
                                <div class="mt-2 text-2xl sm:text-3xl font-bold text-red-600">{{ count($importResult['errors']) }}</div>
                            </div>
                        </div>

                        @if(!empty($importResult['errors']))
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 sm:p-6">
                                <h3 class="font-semibold text-red-900 mb-3">Errors</h3>
                                <ul class="space-y-2 text-sm text-red-700">
                                    @foreach(array_slice($importResult['errors'], 0, 5) as $error)
                                        <li class="flex">
                                            <span class="mr-3 flex-shrink-0">•</span>
                                            <span>{{ $error }}</span>
                                        </li>
                                    @endforeach
                                    @if(count($importResult['errors']) > 5)
                                        <li class="font-semibold italic">... and {{ count($importResult['errors']) - 5 }} more errors</li>
                                    @endif
                                </ul>
                            </div>
                        @endif

                        @if($coverJobsDispatched > 0)
                            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 sm:p-6">
                                <div class="flex items-start">
                                    <svg class="mt-0.5 h-5 w-5 text-blue-400 animate-spin flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-semibold text-blue-900">Fetching book covers...</p>
                                        <p class="mt-1 text-xs text-blue-700">{{ $coverJobsDispatched }} book {{ Str::plural('cover', $coverJobsDispatched) }} queued. This may take a few minutes.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <a href="{{ route('books.index') }}" class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            View Books
                        </a>
                        <button wire:click="resetForm" type="button" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                            Import More
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>

    <main class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Import Result --}}
            @if($importResult)
                <div class="mb-8 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                    <h2 class="text-lg font-semibold text-gray-900">Import Complete</h2>

                    <dl class="mt-4 grid grid-cols-3 gap-4">
                        <div class="rounded-lg bg-green-50 p-4">
                            <dt class="text-sm font-medium text-green-800">Imported</dt>
                            <dd class="mt-1 text-2xl font-semibold text-green-600">{{ $importResult['imported'] }}</dd>
                        </div>
                        <div class="rounded-lg bg-yellow-50 p-4">
                            <dt class="text-sm font-medium text-yellow-800">Skipped (duplicates)</dt>
                            <dd class="mt-1 text-2xl font-semibold text-yellow-600">{{ $importResult['skipped'] }}</dd>
                        </div>
                        <div class="rounded-lg bg-red-50 p-4">
                            <dt class="text-sm font-medium text-red-800">Errors</dt>
                            <dd class="mt-1 text-2xl font-semibold text-red-600">{{ count($importResult['errors']) }}</dd>
                        </div>
                    </dl>

                    @if(!empty($importResult['errors']))
                        <div class="mt-4">
                            <h3 class="text-sm font-medium text-gray-900">Errors:</h3>
                            <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                                @foreach(array_slice($importResult['errors'], 0, 10) as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                @if(count($importResult['errors']) > 10)
                                    <li>... and {{ count($importResult['errors']) - 10 }} more</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    @if($coverJobsDispatched > 0)
                        <div class="mt-4 rounded-md bg-blue-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        Fetching {{ $coverJobsDispatched }} book {{ Str::plural('cover', $coverJobsDispatched) }} in background...
                                    </p>
                                    <p class="mt-1 text-xs text-blue-600">
                                        Covers will appear automatically as they are found.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex gap-4">
                        <a href="{{ route('books.index') }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                            View Your Books
                        </a>
                        <button wire:click="resetForm" type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Import More
                        </button>
                    </div>
                </div>
            @else
                {{-- Instructions --}}
                <div class="mb-8 rounded-lg bg-blue-50 p-6">
                    <h2 class="text-lg font-semibold text-blue-900">How to export from GoodReads</h2>
                    <ol class="mt-3 list-decimal list-inside text-sm text-blue-800 space-y-2">
                        <li>Go to <strong>goodreads.com</strong> and sign in</li>
                        <li>Click on <strong>My Books</strong> in the navigation</li>
                        <li>Click on <strong>Import and Export</strong> (left sidebar)</li>
                        <li>Click <strong>Export Library</strong></li>
                        <li>Wait for the export to complete and download the CSV file</li>
                        <li>Upload that CSV file here</li>
                    </ol>
                </div>

                {{-- Upload Form --}}
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                    <div
                        x-data="{ dragging: false }"
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave.prevent="dragging = false"
                        x-on:drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                        class="relative"
                    >
                        <label
                            for="file"
                            :class="dragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
                            class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">CSV file from GoodReads export (max 10MB)</p>
                            </div>
                            <input
                                wire:model="file"
                                x-ref="fileInput"
                                id="file"
                                type="file"
                                accept=".csv,.txt"
                                class="sr-only"
                            >
                        </label>
                    </div>

                    @error('file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Loading indicator --}}
                    <div wire:loading wire:target="file" class="mt-4 flex items-center gap-2 text-sm text-gray-500">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing file...
                    </div>
                </div>

                {{-- Preview --}}
                @if($preview && $preview->count() > 0)
                    <div class="mt-8 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                        <h2 class="text-lg font-semibold text-gray-900">Preview (first 10 books)</h2>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Title</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Author</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Rating</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($preview as $book)
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 max-w-xs truncate">
                                                {{ $book['title'] }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 max-w-xs truncate">
                                                {{ $book['author'] ?? '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                                    @switch($book['status']->value)
                                                        @case('want_to_read') bg-blue-100 text-blue-700 @break
                                                        @case('reading') bg-yellow-100 text-yellow-700 @break
                                                        @case('read') bg-green-100 text-green-700 @break
                                                    @endswitch
                                                ">
                                                    {{ $book['status']->label() }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if($book['rating'])
                                                    {{ $book['rating'] }}/5
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Import Options --}}
                        <div class="mt-6 flex items-center gap-4">
                            <label class="flex items-center gap-2">
                                <input wire:model="skipDuplicates" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-600">
                                <span class="text-sm text-gray-700">Skip duplicates (by ISBN or GoodReads ID)</span>
                            </label>
                        </div>

                        {{-- Import Button --}}
                        <div class="mt-6 flex gap-4">
                            <button
                                wire:click="import"
                                wire:loading.attr="disabled"
                                type="button"
                                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="import">Import All Books</span>
                                <span wire:loading wire:target="import" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Importing...
                                </span>
                            </button>
                            <button wire:click="resetForm" type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
