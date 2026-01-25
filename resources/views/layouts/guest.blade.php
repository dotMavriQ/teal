<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TEAL') }}</title>

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
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
