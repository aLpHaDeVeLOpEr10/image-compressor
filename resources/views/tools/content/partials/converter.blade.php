{{-- Content sections shared by the converter tools. All text comes from content keys editable in the admin. --}}
@php
    $prefix = $tool->familyKey();
    $steps = \App\Tools\ToolContent::list($content, 'how_to_step', ['title', 'text']);
    $benefits = \App\Tools\ToolContent::list($content, 'benefit', ['title', 'text', 'icon']);
@endphp

@if (trim($content['why_heading'] ?? '') !== '' || trim($content['why_body'] ?? '') !== '')
    <x-content.section :title="$content['why_heading'] ?? ''">
        {!! $content['why_body'] ?? '' !!}
    </x-content.section>
@endif

@if ($steps)
    <section class="section border-y border-line bg-canvas" aria-labelledby="{{ $prefix }}-how-heading">
        <x-ui.container>
            <x-ui.section-heading id="{{ $prefix }}-how-heading" :title="$content['how_to_heading'] ?? ''" />
            <x-content.steps class="mt-10" :steps="$steps" />
        </x-ui.container>
    </section>
@endif

@if (trim($content['details_heading'] ?? '') !== '' || trim($content['details_body'] ?? '') !== '')
    <x-content.section :title="$content['details_heading'] ?? ''">
        {!! $content['details_body'] ?? '' !!}
    </x-content.section>
@endif

@if ($benefits)
    <section class="section border-t border-line bg-canvas" aria-labelledby="{{ $prefix }}-benefits-heading">
        <x-ui.container>
            <x-ui.section-heading id="{{ $prefix }}-benefits-heading" :title="$content['benefits_heading'] ?? ''" />
            <x-content.feature-grid class="mt-10" :items="array_map(fn (array $benefit) => [...$benefit, 'icon' => $benefit['icon'] ?: 'check-circle'], $benefits)" />
        </x-ui.container>
    </section>
@endif
