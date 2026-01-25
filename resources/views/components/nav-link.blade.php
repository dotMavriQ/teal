@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-theme-accent-primary text-sm font-medium leading-5 text-theme-text-primary focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-theme-text-secondary hover:text-theme-text-primary hover:border-theme-border-secondary focus:outline-none focus:text-theme-text-primary focus:border-theme-border-secondary transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
