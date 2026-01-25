<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ auth()->user()?->theme ?? config('themes.default', 'normie') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TEAL') }}</title>

        <!-- Theme meta tag for JavaScript access -->
        <meta name="user-theme" content="{{ auth()->user()?->theme ?? config('themes.default', 'normie') }}">

        <!-- Theme must be set BEFORE CSS loads to prevent flash -->
        <script>
            (function() {
                var storedTheme = localStorage.getItem('teal-theme');
                var metaTheme = document.querySelector('meta[name="user-theme"]')?.content;
                var theme = storedTheme || metaTheme || 'normie';
                document.documentElement.setAttribute('data-theme', theme);
                // Sync localStorage with server theme if empty
                if (!storedTheme && metaTheme && metaTheme !== 'normie') {
                    localStorage.setItem('teal-theme', metaTheme);
                }
            })();
        </script>

        <!-- SEO Meta Tags -->
        <meta name="description" content="TEAL - Personal book library management. Track your reading, import from Goodreads, and organize your books.">
        <meta name="keywords" content="books, reading, library, book tracker, goodreads, reading list">
        <meta name="author" content="TEAL">
        <meta name="robots" content="index, follow">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ config('app.name', 'TEAL') }}">
        <meta property="og:description" content="Personal book library management. Track your reading, import from Goodreads, and organize your books.">
        <meta property="og:image" content="{{ asset('android-chrome-512x512.png') }}">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ config('app.name', 'TEAL') }}">
        <meta name="twitter:description" content="Personal book library management. Track your reading, import from Goodreads, and organize your books.">
        <meta name="twitter:image" content="{{ asset('android-chrome-512x512.png') }}">

        <!-- Favicon -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <meta name="theme-color" content="#1e40af">
        <meta name="msapplication-TileColor" content="#1e40af">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Ensure theme is applied immediately and persists across Livewire navigations -->
        <script>
            (function() {
                var serverTheme = '{{ auth()->user()?->theme ?? config('themes.default', 'normie') }}';
                // Check localStorage first (updated by ThemeSwitcher), fallback to server value
                var storedTheme = localStorage.getItem('teal-theme');
                var userTheme = storedTheme || serverTheme;

                // Update localStorage with server value if it differs (user might have changed it elsewhere)
                if (serverTheme !== 'normie' && serverTheme !== storedTheme) {
                    localStorage.setItem('teal-theme', serverTheme);
                    userTheme = serverTheme;
                }

                document.documentElement.setAttribute('data-theme', userTheme);

                // Listen for theme changes from the ThemeSwitcher component
                document.addEventListener('livewire:initialized', function() {
                    Livewire.on('theme-changed', function(data) {
                        var newTheme = data.theme || data[0]?.theme;
                        if (newTheme) {
                            document.documentElement.setAttribute('data-theme', newTheme);
                            localStorage.setItem('teal-theme', newTheme);
                        }
                    });
                });

                // Re-apply theme after Livewire navigation
                document.addEventListener('livewire:navigated', function() {
                    var savedTheme = localStorage.getItem('teal-theme');
                    if (savedTheme) {
                        document.documentElement.setAttribute('data-theme', savedTheme);
                    }
                });
            })();
        </script>
    </head>
    <body class="font-sans antialiased bg-theme-bg-secondary text-theme-text-primary">
        <div class="min-h-screen">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-theme-bg-primary shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
