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
                            <span class="ml-4 text-sm font-medium text-gray-500" aria-current="page">Import from GoodReads</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">
                Import from GoodReads
            </h1>
        </div>
    </header>

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
    </main>
</div>
