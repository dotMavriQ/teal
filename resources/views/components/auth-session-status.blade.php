@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-theme-success']) }}>
        {{ $status }}
    </div>
@endif
