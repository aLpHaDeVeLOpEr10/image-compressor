@php
    $registry = app(\App\Tools\ToolRegistry::class);
    $site = $registry->siteContent();
    $linkList = fn (string $prefix) => collect(\App\Tools\ToolContent::list($site, $prefix, ['label', 'url']))->mapWithKeys(fn (array $link) => [$link['label'] => $link['url']])->all();
    $columns = array_filter([
        $site['site_footer_tools_heading'] ?? '' => $registry->all()->map(fn ($tool) => $registry->localized($tool->key, app()->getLocale()))->mapWithKeys(fn ($tool) => [$tool->name => $tool->url()])->all(),
        $site['site_footer_company_heading'] ?? '' => $linkList('site_footer_company'),
        $site['site_footer_legal_heading'] ?? '' => $linkList('site_footer_legal'),
    ], fn (array $links, string $heading) => $heading !== '' && $links !== [], ARRAY_FILTER_USE_BOTH);
@endphp

<footer class="border-t border-line bg-surface">
    <x-ui.container class="py-12 sm:py-14">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-5">
                <x-site.logo />
                <p class="mt-4 max-w-sm text-sm leading-6 text-muted">
                    {{ $site['site_footer_tagline'] ?? '' }}
                </p>
                <ul class="mt-5 flex flex-wrap gap-2 text-xs font-medium text-body">
                    @foreach (\App\Tools\ToolContent::list($site, 'site_footer_badge') as $badge)
                        <li class="inline-flex items-center gap-1.5 rounded-full border border-line bg-canvas px-3 py-1">
                            <x-ui.icon name="check" class="size-3.5 text-success-600" stroke-width="2.5" />{{ $badge }}
                        </li>
                    @endforeach
                </ul>
                @if (config('site.social'))
                    <ul class="mt-5 flex flex-wrap gap-x-4 gap-y-2 text-sm">
                        @foreach (config('site.social') as $network => $url)
                            <li><a href="{{ $url }}" class="text-muted hover:text-ink" rel="noopener me" target="_blank">{{ $network }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <nav aria-label="Footer" class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-7">
                @foreach ($columns as $heading => $links)
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-ink uppercase">{{ $heading }}</p>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            @foreach ($links as $label => $url)
                                <li><a href="{{ $url }}" class="text-muted transition-colors hover:text-brand-700">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </div>

        <div class="mt-12 flex flex-col gap-3 border-t border-line pt-6 text-sm text-muted sm:flex-row sm:items-center sm:justify-between">
            <p>{{ $site['site_footer_copyright'] ?? '' }}</p>
            <p class="inline-flex items-center gap-1.5">
                <x-ui.icon name="lock" class="size-4 text-success-600" /> {{ $site['site_footer_note'] ?? '' }}
            </p>
        </div>
    </x-ui.container>
</footer>
