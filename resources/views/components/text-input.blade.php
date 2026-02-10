@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-theme-border-secondary focus:border-theme-accent-primary focus:ring-theme-accent-primary rounded-md shadow-sm']) }}>
