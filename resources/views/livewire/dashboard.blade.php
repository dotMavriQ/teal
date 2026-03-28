<div class="min-h-screen bg-theme-bg-secondary">
    <header class="bg-theme-bg-primary shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-theme-text-primary">
                Your Library
            </h1>
        </div>
    </header>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4" role="list">
                @foreach($categories as $category)
                    @if($category['active'])
                        <a
                            href="{{ route($category['route']) }}"
                            class="relative flex flex-col items-center rounded-lg border-2 border-theme-accent-primary bg-theme-card-bg p-4 sm:p-6 shadow-sm ring-1 ring-theme-border-primary hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-theme-accent-primary"
                            role="listitem"
                        >
                            <div class="flex h-12 w-12 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-theme-bg-tertiary mb-3 sm:mb-4">
                                @if($category['icon'] === 'film')
                                    {{-- Lucide: film --}}
                                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-theme-accent-primary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect width="18" height="18" x="3" y="3" rx="2" />
                                        <path d="M7 3v18" /><path d="M3 7.5h4" /><path d="M3 12h18" /><path d="M3 16.5h4" />
                                        <path d="M17 3v18" /><path d="M17 7.5h4" /><path d="M17 16.5h4" />
                                    </svg>
                                @elseif($category['icon'] === 'book-open')
                                    {{-- Lucide: book-open --}}
                                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-theme-accent-primary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 7v14" />
                                        <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z" />
                                    </svg>
                                @elseif($category['icon'] === 'headphones')
                                    {{-- Lucide: headphones --}}
                                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-theme-accent-primary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3" />
                                    </svg>
                                @elseif($category['icon'] === 'game-controller')
                                    {{-- Lucide: gamepad-2 --}}
                                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-theme-accent-primary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <line x1="6" x2="10" y1="11" y2="11" /><line x1="8" x2="8" y1="9" y2="13" />
                                        <line x1="15" x2="15.01" y1="12" y2="12" /><line x1="18" x2="18.01" y1="10" y2="10" />
                                        <path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="text-lg sm:text-xl font-semibold text-theme-text-primary text-center">{{ $category['name'] }}</h3>
                            <p class="mt-1 text-xs sm:text-sm text-theme-text-secondary text-center">{{ $category['description'] }}</p>

                            {{-- Compact stats --}}
                            @if($category['name'] === 'Watching' && ($watchingStats['total_movies'] > 0 || $animeStats['total_anime'] > 0))
                                <div class="mt-3 flex flex-wrap justify-center gap-2 sm:gap-3 text-[10px] sm:text-xs text-theme-text-secondary">
                                    @if($watchingStats['total_movies'] > 0)
                                        <span>{{ $watchingStats['total_movies'] }} movies</span>
                                    @endif
                                    @if($animeStats['total_anime'] > 0)
                                        <span>{{ $animeStats['total_anime'] }} anime</span>
                                    @endif
                                </div>
                            @elseif($category['name'] === 'Playing' && $playingStats['total_games'] > 0)
                                <div class="mt-3 flex flex-wrap justify-center gap-2 sm:gap-3 text-[10px] sm:text-xs text-theme-text-secondary">
                                    <span>{{ $playingStats['total_games'] }} games</span>
                                    @if($playingStats['currently_playing'] > 0)
                                        <span class="text-theme-status-playing">&middot; {{ $playingStats['currently_playing'] }} playing</span>
                                    @endif
                                </div>
                            @elseif($category['name'] === 'Listening' && ($listeningStats['total_concerts'] > 0 || $listeningStats['total_albums'] > 0))
                                <div class="mt-3 flex flex-wrap justify-center gap-2 sm:gap-3 text-[10px] sm:text-xs text-theme-text-secondary">
                                    @if($listeningStats['total_concerts'] > 0)
                                        <span>{{ $listeningStats['total_concerts'] }} concerts</span>
                                    @endif
                                    @if($listeningStats['total_albums'] > 0)
                                        <span>{{ $listeningStats['total_albums'] }} albums</span>
                                    @endif
                                    @if($listeningStats['upcoming'] > 0)
                                        <span class="text-theme-accent-primary">&middot; {{ $listeningStats['upcoming'] }} upcoming</span>
                                    @endif
                                </div>
                            @elseif($category['name'] === 'Reading' && ($readingStats['total_books'] > 0 || $readingStats['total_comics'] > 0))
                                <div class="mt-3 flex flex-wrap justify-center gap-2 sm:gap-3 text-[10px] sm:text-xs text-theme-text-secondary">
                                    @if($readingStats['total_books'] > 0)
                                        <span>{{ $readingStats['total_books'] }} books</span>
                                    @endif
                                    @if($readingStats['total_comics'] > 0)
                                        <span>{{ $readingStats['total_comics'] }} comics</span>
                                    @endif
                                    @if($readingStats['currently_reading'] > 0)
                                        <span class="text-theme-status-reading">&middot; {{ $readingStats['currently_reading'] }} reading</span>
                                    @endif
                                </div>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </main>
</div>
