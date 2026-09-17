<x-layouts.app :seo="$seo">
    <section class="border-b border-line bg-canvas">
        <x-ui.container class="pt-6 pb-12 sm:pt-8 sm:pb-16">
            <x-ui.breadcrumbs :items="$seo->breadcrumbs" />

            <div class="mx-auto mt-6 max-w-3xl text-center">
                <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $tool->h1 }}</h1>
                <p class="mt-3 text-base leading-7 text-muted sm:text-lg">{{ $tool->intro }}</p>
            </div>

            @if ($languageVersions->count() > 1)
                <div class="mt-5 flex justify-end">
                    <x-tools.language-switcher :versions="$languageVersions" :label="$content['language_switcher_label'] ?? 'Language'" />
                </div>
            @endif

            <div class="mt-3">
                @include($tool->widgetView())
            </div>

            <x-tools.category-nav :tools="$categoryTools" :current="$tool->key" :label="$categoryLabel" class="mt-6" />
        </x-ui.container>
    </section>

    @php($heroPointIcons = ['check-circle', 'user-x', 'images', 'upload'])
    @php($heroPoints = collect(range(1, count($heroPointIcons)))
        ->filter(fn (int $number) => trim($content["hero_point_{$number}"] ?? '') !== '')
        ->map(fn (int $number) => [
            'icon' => $heroPointIcons[$number - 1],
            'title' => $content["hero_point_{$number}"],
            'text' => $content["hero_point_{$number}_text"] ?? '',
        ])
        ->values()
        ->all())

    @if ($heroPoints)
        <section class="section pb-0" aria-labelledby="highlights-heading">
            <x-ui.container>
                <x-ui.section-heading id="highlights-heading" :title="$content['hero_points_heading'] ?? ''" />
                <x-content.feature-grid class="mt-10" :items="$heroPoints" />
            </x-ui.container>
        </section>
    @endif

    @if ($tool->summary !== '' || $figure)
        <section class="section pb-0" aria-label="{{ $content['summary_label'] ?? '' }}">
            <x-ui.container>
                <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] lg:items-start">
                    @if ($tool->summary !== '')
                        <x-content.summary :title="$content['summary_label'] ?? ''" :updated-label="$content['summary_updated_label'] ?? ''" :updated="$tool->updatedAt">{{ $tool->summary }}</x-content.summary>
                    @endif
                    <x-content.figure :figure="$figure" />
                </div>
            </x-ui.container>
        </section>
    @endif

    @if ($groups->isNotEmpty())
        @include('tools.partials.directory')
    @endif

    @include($tool->contentView())

    <x-content.faq-section
        :faqs="$tool->faqs"
        :title="$content['faq_heading'] ?? ''"
        :description="($content['faq_description'] ?? '') ?: null"
        :more="$content['faq_more_html'] ?? ''"
        class="border-t border-line"
    />

    <x-tools.related-tools
        :tools="$relatedTools"
        :title="$content['related_heading'] ?? ''"
        :description="($content['related_description'] ?? '') ?: null"
        :cta="$content['tool_card_cta'] ?? ''"
    />
</x-layouts.app>
