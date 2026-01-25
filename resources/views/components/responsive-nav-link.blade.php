@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-theme-accent-primary text-start text-base font-medium text-theme-accent-primary bg-theme-bg-tertiary focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-theme-text-secondary hover:text-theme-text-primary hover:bg-theme-bg-hover hover:border-theme-border-secondary focus:outline-none focus:text-theme-text-primary focus:bg-theme-bg-hover focus:border-theme-border-secondary transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
