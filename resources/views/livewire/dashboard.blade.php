<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                Your Library
            </h1>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Reading Stats --}}
            @if($readingStats['total_books'] > 0)
                <div class="mb-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Reading Stats</h2>
                    <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                            <dt class="truncate text-sm font-medium text-gray-500">Currently Reading</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight text-yellow-600">
                                {{ $readingStats['currently_reading'] }}
                            </dd>
                        </div>
                        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                            <dt class="truncate text-sm font-medium text-gray-500">Read This Year</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight text-green-600">
                                {{ $readingStats['read_this_year'] }}
                            </dd>
                        </div>
                        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                            <dt class="truncate text-sm font-medium text-gray-500">Total Books</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight text-blue-600">
                                {{ $readingStats['total_books'] }}
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            {{-- Category Cards --}}
            <h2 class="text-lg font-medium text-gray-900 mb-4">Categories</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4" role="list">
                @foreach($categories as $category)
                    @if($category['active'])
                        <a
                            href="{{ route($category['route']) }}"
                            class="relative flex flex-col items-center rounded-lg border-2 border-{{ $category['color'] }}-500 bg-white p-6 shadow-sm hover:shadow-lg hover:border-{{ $category['color'] }}-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-{{ $category['color'] }}-500 focus:ring-offset-2"
                            role="listitem"
                        >
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-{{ $category['color'] }}-100 mb-4">
                                @if($category['icon'] === 'book-open')
                                    <svg class="h-8 w-8 text-{{ $category['color'] }}-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $category['name'] }}</h3>
                            <p class="mt-1 text-sm text-gray-500 text-center">{{ $category['description'] }}</p>
                        </a>
                    @else
                        <div
                            class="relative flex flex-col items-center rounded-lg border-2 border-gray-200 bg-gray-50 p-6 opacity-60 cursor-not-allowed"
                            role="listitem"
                            aria-disabled="true"
                        >
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-200 mb-4">
                                @if($category['icon'] === 'film')
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M18 18.375v-5.25m0 5.25v-1.5c0-.621.504-1.125 1.125-1.125M18 13.125v1.5c0 .621.504 1.125 1.125 1.125M18 13.125c0-.621.504-1.125 1.125-1.125M6 13.125v1.5c0 .621-.504 1.125-1.125 1.125M6 13.125C6 12.504 5.496 12 4.875 12m-1.5 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M19.125 12h1.5m0 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h1.5m14.25 0h1.5" />
                                    </svg>
                                @elseif($category['icon'] === 'puzzle-piece')
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z" />
                                    </svg>
                                @elseif($category['icon'] === 'musical-note')
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="text-xl font-semibold text-gray-500">{{ $category['name'] }}</h3>
                            <p class="mt-1 text-sm text-gray-400 text-center">{{ $category['description'] }}</p>
                            <span class="mt-2 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                Coming Soon
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </main>
</div>
