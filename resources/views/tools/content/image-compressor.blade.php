@php
    $steps = \App\Tools\ToolContent::list($content, 'how_to_step', ['title', 'text']);
    $sections = [
        ['id' => 'modes', 'key' => 'modes', 'muted' => true, 'figure' => null],
        ['id' => 'jpg', 'key' => 'jpg', 'muted' => false, 'figure' => 'jpg-quality-ladder'],
        ['id' => 'png', 'key' => 'png', 'muted' => true, 'figure' => null],
        ['id' => 'webp', 'key' => 'webp', 'muted' => false, 'figure' => null],
        ['id' => 'target-size', 'key' => 'target', 'muted' => true, 'figure' => 'target-100kb'],
    ];
@endphp

@if ($steps)
    <section class="section" aria-labelledby="how-to-heading">
        <x-ui.container>
            <x-ui.section-heading id="how-to-heading" :title="$content['how_to_heading'] ?? ''" :description="($content['how_to_description'] ?? '') ?: null" />
            <x-content.steps class="mt-10" :steps="$steps" />
        </x-ui.container>
    </section>
@endif

@foreach ($sections as $section)
    @if (trim($content["{$section['key']}_heading"] ?? '') !== '' || trim($content["{$section['key']}_body"] ?? '') !== '')
        <x-content.section :title="$content[$section['key'].'_heading'] ?? ''" :id="$section['id']" :muted="$section['muted']">
            {!! $content["{$section['key']}_body"] ?? '' !!}
            @if ($section['figure'])
                <x-content.figure :figure="\App\Tools\ToolContent::figure(\App\Support\Figures::get($section['figure']), $content, $section['key'].'_figure')" class="mt-8" />
            @endif
        </x-content.section>
    @endif
@endforeach

@if (trim($content['formats_heading'] ?? '') !== '' || trim($content['formats_body'] ?? '') !== '')
    <section class="section border-t border-line" aria-labelledby="formats-heading">
        <x-ui.container>
            <x-ui.section-heading id="formats-heading" :title="$content['formats_heading'] ?? ''" :description="($content['formats_description'] ?? '') ?: null" />
            <div class="prose-content mx-auto mt-8 max-w-3xl">
                {!! $content['formats_body'] ?? '' !!}
            </div>
        </x-ui.container>
    </section>
@endif
