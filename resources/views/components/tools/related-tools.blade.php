@props(['tools', 'title' => 'Related tools', 'description' => null, 'cta' => 'Open tool'])

@if ($tools->isNotEmpty())
    <section {{ $attributes->class('section border-t border-line bg-canvas') }} aria-labelledby="related-tools-heading">
        <x-ui.container>
            <x-ui.section-heading id="related-tools-heading" :title="$title" :description="$description" />
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($tools as $tool)
                    <x-tools.tool-card :tool="$tool" :cta="$cta" />
                @endforeach
            </div>
        </x-ui.container>
    </section>
@endif
