import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Theme-aware colors using CSS variables
                theme: {
                    bg: {
                        primary: 'rgb(var(--color-bg-primary) / <alpha-value>)',
                        secondary: 'rgb(var(--color-bg-secondary) / <alpha-value>)',
                        tertiary: 'rgb(var(--color-bg-tertiary) / <alpha-value>)',
                        hover: 'rgb(var(--color-bg-hover) / <alpha-value>)',
                        active: 'rgb(var(--color-bg-active) / <alpha-value>)',
                    },
                    text: {
                        primary: 'rgb(var(--color-text-primary) / <alpha-value>)',
                        secondary: 'rgb(var(--color-text-secondary) / <alpha-value>)',
                        tertiary: 'rgb(var(--color-text-tertiary) / <alpha-value>)',
                        muted: 'rgb(var(--color-text-muted) / <alpha-value>)',
                        inverted: 'rgb(var(--color-text-inverted) / <alpha-value>)',
                    },
                    border: {
                        primary: 'rgb(var(--color-border-primary) / <alpha-value>)',
                        secondary: 'rgb(var(--color-border-secondary) / <alpha-value>)',
                        focus: 'rgb(var(--color-border-focus) / <alpha-value>)',
                    },
                    accent: {
                        primary: 'rgb(var(--color-accent-primary) / <alpha-value>)',
                        'primary-hover': 'rgb(var(--color-accent-primary-hover) / <alpha-value>)',
                        secondary: 'rgb(var(--color-accent-secondary) / <alpha-value>)',
                    },
                    success: {
                        DEFAULT: 'rgb(var(--color-success) / <alpha-value>)',
                        bg: 'rgb(var(--color-success-bg) / <alpha-value>)',
                        text: 'rgb(var(--color-success-text) / <alpha-value>)',
                    },
                    warning: {
                        DEFAULT: 'rgb(var(--color-warning) / <alpha-value>)',
                        bg: 'rgb(var(--color-warning-bg) / <alpha-value>)',
                        text: 'rgb(var(--color-warning-text) / <alpha-value>)',
                    },
                    danger: {
                        DEFAULT: 'rgb(var(--color-danger) / <alpha-value>)',
                        bg: 'rgb(var(--color-danger-bg) / <alpha-value>)',
                        text: 'rgb(var(--color-danger-text) / <alpha-value>)',
                    },
                    info: {
                        DEFAULT: 'rgb(var(--color-info) / <alpha-value>)',
                        bg: 'rgb(var(--color-info-bg) / <alpha-value>)',
                        text: 'rgb(var(--color-info-text) / <alpha-value>)',
                    },
                    status: {
                        want: 'rgb(var(--color-status-want) / <alpha-value>)',
                        'want-bg': 'rgb(var(--color-status-want-bg) / <alpha-value>)',
                        reading: 'rgb(var(--color-status-reading) / <alpha-value>)',
                        'reading-bg': 'rgb(var(--color-status-reading-bg) / <alpha-value>)',
                        read: 'rgb(var(--color-status-read) / <alpha-value>)',
                        'read-bg': 'rgb(var(--color-status-read-bg) / <alpha-value>)',
                        watchlist: 'rgb(var(--color-status-watchlist) / <alpha-value>)',
                        'watchlist-bg': 'rgb(var(--color-status-watchlist-bg) / <alpha-value>)',
                        watching: 'rgb(var(--color-status-watching) / <alpha-value>)',
                        'watching-bg': 'rgb(var(--color-status-watching-bg) / <alpha-value>)',
                        watched: 'rgb(var(--color-status-watched) / <alpha-value>)',
                        'watched-bg': 'rgb(var(--color-status-watched-bg) / <alpha-value>)',
                        backlog: 'rgb(var(--color-status-backlog) / <alpha-value>)',
                        'backlog-bg': 'rgb(var(--color-status-backlog-bg) / <alpha-value>)',
                        playing: 'rgb(var(--color-status-playing) / <alpha-value>)',
                        'playing-bg': 'rgb(var(--color-status-playing-bg) / <alpha-value>)',
                        shelved: 'rgb(var(--color-status-shelved) / <alpha-value>)',
                        'shelved-bg': 'rgb(var(--color-status-shelved-bg) / <alpha-value>)',
                        completed: 'rgb(var(--color-status-completed) / <alpha-value>)',
                        'completed-bg': 'rgb(var(--color-status-completed-bg) / <alpha-value>)',
                        mastered: 'rgb(var(--color-status-mastered) / <alpha-value>)',
                        'mastered-bg': 'rgb(var(--color-status-mastered-bg) / <alpha-value>)',
                    },
                    star: {
                        filled: 'rgb(var(--color-star-filled) / <alpha-value>)',
                        empty: 'rgb(var(--color-star-empty) / <alpha-value>)',
                    },
                    platform: {
                        nintendo: 'rgb(var(--color-platform-nintendo) / <alpha-value>)',
                        'nintendo-bg': 'rgb(var(--color-platform-nintendo-bg) / <alpha-value>)',
                        'nintendo-logo': 'rgb(var(--color-platform-nintendo-logo) / <alpha-value>)',
                        steam: 'rgb(var(--color-platform-steam) / <alpha-value>)',
                        'steam-bg': 'rgb(var(--color-platform-steam-bg) / <alpha-value>)',
                        'steam-logo': 'rgb(var(--color-platform-steam-logo) / <alpha-value>)',
                        playstation: 'rgb(var(--color-platform-playstation) / <alpha-value>)',
                        'playstation-bg': 'rgb(var(--color-platform-playstation-bg) / <alpha-value>)',
                        'playstation-logo': 'rgb(var(--color-platform-playstation-logo) / <alpha-value>)',
                        xbox: 'rgb(var(--color-platform-xbox) / <alpha-value>)',
                        'xbox-bg': 'rgb(var(--color-platform-xbox-bg) / <alpha-value>)',
                        'xbox-logo': 'rgb(var(--color-platform-xbox-logo) / <alpha-value>)',
                        gog: 'rgb(var(--color-platform-gog) / <alpha-value>)',
                        'gog-bg': 'rgb(var(--color-platform-gog-bg) / <alpha-value>)',
                        'gog-logo': 'rgb(var(--color-platform-gog-logo) / <alpha-value>)',
                        pc: 'rgb(var(--color-platform-pc) / <alpha-value>)',
                        'pc-bg': 'rgb(var(--color-platform-pc-bg) / <alpha-value>)',
                        'pc-logo': 'rgb(var(--color-platform-pc-logo) / <alpha-value>)',
                        default: 'rgb(var(--color-platform-default) / <alpha-value>)',
                        'default-bg': 'rgb(var(--color-platform-default-bg) / <alpha-value>)',
                        'default-logo': 'rgb(var(--color-platform-default-logo) / <alpha-value>)',
                    },
                    card: {
                        bg: 'rgb(var(--color-card-bg) / <alpha-value>)',
                        border: 'rgb(var(--color-card-border) / <alpha-value>)',
                    },
                    input: {
                        bg: 'rgb(var(--color-input-bg) / <alpha-value>)',
                        border: 'rgb(var(--color-input-border) / <alpha-value>)',
                        focus: 'rgb(var(--color-input-focus-ring) / <alpha-value>)',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
