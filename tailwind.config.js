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
                    },
                    star: {
                        filled: 'rgb(var(--color-star-filled) / <alpha-value>)',
                        empty: 'rgb(var(--color-star-empty) / <alpha-value>)',
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
