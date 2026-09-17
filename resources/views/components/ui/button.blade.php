@props(['href' => null, 'variant' => 'primary', 'size' => null, 'icon' => null, 'iconRight' => null])

@php
    $classes = collect(['btn', "btn-{$variant}", $size ? "btn-{$size}" : null])->filter()->implode(' ');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" class="size-4.5" />@endif
        {{ $slot }}
        @if ($iconRight)<x-ui.icon :name="$iconRight" class="size-4.5" />@endif
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" class="size-4.5" />@endif
        {{ $slot }}
        @if ($iconRight)<x-ui.icon :name="$iconRight" class="size-4.5" />@endif
    </button>
@endif
