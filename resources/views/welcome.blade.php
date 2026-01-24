<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>TEAL - The Essential Aggregator Library</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500&family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-gray-950 text-gray-100 min-h-screen">
        <div class="relative min-h-screen flex flex-col">
            <!-- Navigation -->
            <header class="absolute top-0 right-0 p-6">
                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="rounded-md px-4 py-2 text-sm font-medium text-teal-400 hover:text-teal-300 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="rounded-md px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500"
                            >
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950"
                                >
                                    Register
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <!-- Main Content -->
            <main class="flex-1 flex flex-col items-center justify-center px-6 py-24">
                <!-- ASCII Logo -->
                <div class="mb-8" aria-hidden="true">
                    <pre class="font-mono text-teal-400 text-xs sm:text-sm md:text-base lg:text-lg leading-tight select-none">
 _____  ___   __   _
|_   _|| __| / _\ | |
  | |  | _| | v | | |__
  |_|  |___||_|_| |____|
                    </pre>
                </div>

                <h1 class="sr-only">TEAL - The Essential Aggregator Library</h1>

                <!-- Tagline -->
                <p class="text-xl sm:text-2xl text-gray-400 font-light tracking-wide mb-4">
                    The Essential Aggregator Library
                </p>

                <!-- Description -->
                <p class="max-w-md text-center text-gray-500 mb-12">
                    Track what you're watching, reading, playing, and listening to.
                    All in one place. Your personal media library.
                </p>

                <!-- Category Icons -->
                <div class="flex items-center gap-8 mb-12">
                    <div class="flex flex-col items-center gap-2 text-gray-600 group">
                        <div class="w-12 h-12 rounded-lg bg-gray-900 border border-gray-800 flex items-center justify-center group-hover:border-teal-800 group-hover:text-teal-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-xs uppercase tracking-wider">Watching</span>
                    </div>

                    <div class="flex flex-col items-center gap-2 text-teal-500 group">
                        <div class="w-12 h-12 rounded-lg bg-teal-950 border border-teal-800 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span class="text-xs uppercase tracking-wider">Reading</span>
                    </div>

                    <div class="flex flex-col items-center gap-2 text-gray-600 group">
                        <div class="w-12 h-12 rounded-lg bg-gray-900 border border-gray-800 flex items-center justify-center group-hover:border-teal-800 group-hover:text-teal-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <span class="text-xs uppercase tracking-wider">Playing</span>
                    </div>

                    <div class="flex flex-col items-center gap-2 text-gray-600 group">
                        <div class="w-12 h-12 rounded-lg bg-gray-900 border border-gray-800 flex items-center justify-center group-hover:border-teal-800 group-hover:text-teal-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                        <span class="text-xs uppercase tracking-wider">Listening</span>
                    </div>
                </div>

                <!-- CTA -->
                @guest
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-6 py-3 text-sm font-semibold text-white hover:bg-teal-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950"
                    >
                        Get Started
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endguest
            </main>

            <!-- Footer -->
            <footer class="py-6 text-center text-xs text-gray-600">
                <p>Your personal media library</p>
            </footer>
        </div>
    </body>
</html>
