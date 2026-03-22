<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-theme-bg-primary border-b border-theme-border-primary">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-theme-text-primary" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Watching Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-theme-text-secondary hover:text-theme-text-primary hover:border-theme-border-secondary focus:outline-none focus:text-theme-text-primary focus:border-theme-border-secondary transition duration-150 ease-in-out h-16 {{ request()->routeIs('watching.*', 'movies.*', 'anime.*') ? 'border-theme-accent-primary text-theme-text-primary' : '' }}">
                                    <div>Watching</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('watching.index')" wire:navigate>
                                    {{ __('All Watching') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('movies.index')" wire:navigate>
                                    {{ __('Movies') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('anime.index')" wire:navigate>
                                    {{ __('Anime') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Playing Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-theme-text-secondary hover:text-theme-text-primary hover:border-theme-border-secondary focus:outline-none focus:text-theme-text-primary focus:border-theme-border-secondary transition duration-150 ease-in-out h-16 {{ request()->routeIs('playing.*', 'games.*', 'board-games.*') ? 'border-theme-accent-primary text-theme-text-primary' : '' }}">
                                    <div>Playing</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('playing.index')" wire:navigate>
                                    {{ __('All Playing') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('games.index')" wire:navigate>
                                    {{ __('Games') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('board-games.index')" wire:navigate>
                                    {{ __('Board Games') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Reading Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-theme-text-secondary hover:text-theme-text-primary hover:border-theme-border-secondary focus:outline-none focus:text-theme-text-primary focus:border-theme-border-secondary transition duration-150 ease-in-out h-16 {{ request()->routeIs('reading.*', 'books.*', 'comics.*') ? 'border-theme-accent-primary text-theme-text-primary' : '' }}">
                                    <div>Reading</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('reading.index')" wire:navigate>
                                    {{ __('All Reading') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('books.index')" wire:navigate>
                                    {{ __('Books') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('comics.index')" wire:navigate>
                                    {{ __('Comics') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-theme-text-secondary bg-theme-bg-primary hover:text-theme-text-primary focus:outline-none transition duration-150 ease-in-out">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-on:profile-updated.window="name = $event.detail.name">
                                <span x-text="name">{{ auth()->user()->name }}</span>
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-theme-text-muted hover:text-theme-text-secondary hover:bg-theme-bg-hover focus:outline-none focus:bg-theme-bg-hover focus:text-theme-text-secondary transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-2 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Watching Section -->
        <div class="pt-3 pb-1 border-t border-theme-border-primary">
            <div class="px-4 text-[10px] font-bold text-theme-text-muted uppercase tracking-widest">
                Watching
            </div>
            <div class="mt-1 space-y-0.5">
                <x-responsive-nav-link :href="route('watching.index')" :active="request()->routeIs('watching.index')" wire:navigate>
                    {{ __('All Watching') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('movies.index')" :active="request()->routeIs('movies.*')" wire:navigate>
                    {{ __('Movies') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('anime.index')" :active="request()->routeIs('anime.*')" wire:navigate>
                    {{ __('Anime') }}
                </x-responsive-nav-link>
            </div>
        </div>

        <!-- Playing Section -->
        <div class="pt-3 pb-1 border-t border-theme-border-primary">
            <div class="px-4 text-[10px] font-bold text-theme-text-muted uppercase tracking-widest">
                Playing
            </div>
            <div class="mt-1 space-y-0.5">
                <x-responsive-nav-link :href="route('playing.index')" :active="request()->routeIs('playing.index')" wire:navigate>
                    {{ __('All Playing') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('games.index')" :active="request()->routeIs('games.*')" wire:navigate>
                    {{ __('Games') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('board-games.index')" :active="request()->routeIs('board-games.*')" wire:navigate>
                    {{ __('Board Games') }}
                </x-responsive-nav-link>
            </div>
        </div>

        <!-- Reading Section -->
        <div class="pt-3 pb-1 border-t border-theme-border-primary">
            <div class="px-4 text-[10px] font-bold text-theme-text-muted uppercase tracking-widest">
                Reading
            </div>
            <div class="mt-1 space-y-0.5">
                <x-responsive-nav-link :href="route('reading.index')" :active="request()->routeIs('reading.index')" wire:navigate>
                    {{ __('All Reading') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('books.index')" :active="request()->routeIs('books.*')" wire:navigate>
                    {{ __('Books') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('comics.index')" :active="request()->routeIs('comics.*')" wire:navigate>
                    {{ __('Comics') }}
                </x-responsive-nav-link>
            </div>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-3 pb-2 border-t border-theme-border-primary">
            <div class="px-4">
                <div class="font-semibold text-sm text-theme-text-primary" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-on:profile-updated.window="name = $event.detail.name">
                    <span x-text="name">{{ auth()->user()->name }}</span>
                </div>
                <div class="font-medium text-xs text-theme-text-tertiary">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-2 space-y-0.5">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
