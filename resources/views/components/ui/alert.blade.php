@props(['type' => 'info', 'title' => null])

@php
    $styles = [
        'success' => ['border-success-600/25 bg-success-50 text-success-700', 'check-circle'],
        'error' => ['border-danger-200 bg-danger-50 text-danger-700', 'alert'],
        'warning' => ['border-warning-200 bg-warning-50 text-warning-800', 'alert'],
        'info' => ['border-brand-200 bg-brand-50 text-brand-800', 'info'],
    ][$type];
@endphp

<div {{ $attributes->class("flex gap-3 rounded-lg border px-4 py-3 text-sm {$styles[0]}") }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    <x-ui.icon :name="$styles[1]" class="mt-0.5 size-4.5" />
    <div class="min-w-0">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div>{{ $slot }}</div>
    </div>
</div>
