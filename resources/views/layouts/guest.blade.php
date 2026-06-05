<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ auth()->user()?->theme ?? config('themes.default', 'teal-2026') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TEAL') }}</title>
        <meta name="description" content="TEAL — a self-hosted tracker for everything you read, watch, play and listen to.">

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <meta name="theme-color" content="#7FE3E6">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-theme-bg-primary text-theme-text-primary">
        <div class="min-h-screen flex flex-col justify-center items-center px-6 py-10">
            <a href="/" wire:navigate class="mb-8">
                <img src="{{ asset('brand/seal-hero.svg') }}" alt="TEAL"
                     class="h-44 w-auto" style="filter: drop-shadow(4px 5px 0 rgba(31,50,49,.16));">
            </a>

            <div class="w-full sm:max-w-md px-7 py-7 bg-theme-card-bg border-2 border-theme-text-primary rounded-xl shadow-lg">
                {{ $slot }}
            </div>
        </div>

        @livewireScripts
    </body>
</html>
