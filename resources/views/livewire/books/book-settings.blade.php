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
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-border-secondary" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('reading.index') }}" class="ml-4 text-sm font-medium text-theme-text-muted hover:text-theme-text-primary">Reading</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-border-secondary" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('books.index') }}" class="ml-4 text-sm font-medium text-theme-text-muted hover:text-theme-text-primary">Books</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-theme-border-secondary" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-theme-text-muted" aria-current="page">Settings</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="mt-2">
                <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">
                    Settings
                </h1>
            </div>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Flash Message --}}
            @if (session()->has('message'))
                <div class="rounded-md bg-green-50 p-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Metadata Enrichment Card --}}
            <a href="{{ route('books.metadata') }}" class="block bg-theme-card-bg shadow rounded-lg hover:shadow-md transition-shadow">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 p-3 bg-purple-100 rounded-lg">
                                <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-medium text-theme-text-primary">Metadata Enrichment</h2>
                                <p class="mt-1 text-sm text-theme-text-muted">Fetch missing book metadata from OpenLibrary</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Cover Management Card --}}
            <div class="bg-theme-card-bg shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium leading-6 text-theme-text-primary">Cover Management</h2>
                    <p class="mt-1 text-sm text-theme-text-muted">Book covers are cached locally for faster loading.</p>

                    <div class="mt-6">
                        <button
                            wire:click="recacheCovers"
                            wire:confirm="This will re-download all book covers. Continue?"
                            wire:loading.attr="disabled"
                            wire:target="recacheCovers"
                            type="button"
                            class="inline-flex items-center rounded-md bg-theme-accent-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-theme-accent-primary/80 disabled:opacity-50"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span wire:loading.remove wire:target="recacheCovers">Re-cache All Covers</span>
                            <span wire:loading wire:target="recacheCovers">Queuing...</span>
                        </button>
                        <p class="mt-2 text-xs text-theme-text-muted">Downloads fresh covers from OpenLibrary and stores them locally.</p>
                    </div>
                </div>
            </div>

            {{-- Library Management Card --}}
            <div class="bg-theme-card-bg shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium leading-6 text-theme-text-primary">Danger Zone</h2>
                    <p class="mt-1 text-sm text-theme-text-muted">Irreversible actions.</p>

                    <div class="mt-6">
                        <button
                            wire:click="openDeleteAllModal"
                            type="button"
                            class="inline-flex items-center rounded-md bg-theme-danger px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-theme-danger/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-theme-danger"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete All Books
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Delete All Confirmation Modal --}}
    @if($showDeleteAllModal)
        <div
            x-data="{ confirmWord: '{{ $confirmationWord }}', userInput: @entangle('confirmationInput') }"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div wire:click="closeDeleteAllModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="relative transform overflow-hidden rounded-lg bg-theme-card-bg px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-theme-text-primary" id="modal-title">Delete All Books</h3>
                            <div class="mt-2">
                                <p class="text-sm text-theme-text-muted">
                                    This action <strong>cannot be undone</strong>. This will permanently delete all your books from the library.
                                </p>
                                <p class="mt-3 text-sm text-theme-text-primary">
                                    Please type <code class="rounded bg-theme-bg-tertiary px-2 py-1 font-mono text-theme-danger font-semibold" x-text="confirmWord"></code> to confirm.
                                </p>
                                <div class="mt-3">
                                    <input
                                        x-model="userInput"
                                        type="text"
                                        class="block w-full rounded-md border-0 py-1.5 text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary placeholder:text-theme-text-muted focus:ring-2 focus:ring-inset focus:ring-theme-danger sm:text-sm sm:leading-6 font-mono"
                                        placeholder="Type the confirmation word"
                                        autocomplete="off"
                                    >
                                    @error('confirmationInput')
                                        <p class="mt-1 text-sm text-theme-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                        <button
                            wire:click="deleteAllBooks"
                            type="button"
                            class="inline-flex w-full justify-center rounded-md bg-theme-danger px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-theme-danger/80 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
                            x-bind:disabled="userInput !== confirmWord"
                        >
                            Delete All Books
                        </button>
                        <button
                            wire:click="closeDeleteAllModal"
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-theme-card-bg px-3 py-2 text-sm font-semibold text-theme-text-primary shadow-sm ring-1 ring-inset ring-theme-border-primary hover:bg-theme-bg-hover sm:mt-0 sm:w-auto"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
