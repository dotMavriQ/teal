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
                            <span class="ml-4 text-sm font-medium text-gray-500" aria-current="page">Reading</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">
                Reading
            </h1>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Choose a category</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2" role="list">
                @foreach($subcategories as $category)
                    @if($category['active'])
                        <a
                            href="{{ route($category['route']) }}"
                            class="relative flex flex-col items-center rounded-lg border-2 border-{{ $category['color'] }}-500 bg-white p-8 shadow-sm hover:shadow-lg hover:border-{{ $category['color'] }}-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-{{ $category['color'] }}-500 focus:ring-offset-2"
                            role="listitem"
                        >
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-{{ $category['color'] }}-100 mb-4">
                                @if($category['icon'] === 'book-open')
                                    <svg class="h-10 w-10 text-{{ $category['color'] }}-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="text-2xl font-semibold text-gray-900">{{ $category['name'] }}</h3>
                            <p class="mt-1 text-sm text-gray-500 text-center">{{ $category['description'] }}</p>
                        </a>
                    @else
                        <div
                            class="relative flex flex-col items-center rounded-lg border-2 border-gray-200 bg-gray-50 p-8 opacity-60 cursor-not-allowed"
                            role="listitem"
                            aria-disabled="true"
                        >
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-200 mb-4">
                                @if($category['icon'] === 'squares-2x2')
                                    <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="text-2xl font-semibold text-gray-500">{{ $category['name'] }}</h3>
                            <p class="mt-1 text-sm text-gray-400 text-center">{{ $category['description'] }}</p>
                            <span class="mt-3 inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600">
                                Coming Soon
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </main>
</div>
