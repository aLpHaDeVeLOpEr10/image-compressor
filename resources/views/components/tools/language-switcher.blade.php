@props(['versions', 'label' => 'Language'])

@php($current = $versions->firstWhere('current', true) ?? $versions->first())

<details {{ $attributes->class('group relative ml-auto') }} data-dropdown>
    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-lg border border-line-strong bg-surface px-3.5 py-2 text-sm font-medium text-ink shadow-card transition-colors hover:border-brand-300 hover:bg-brand-50 [&::-webkit-details-marker]:hidden" aria-label="{{ $label }}: {{ $current['name'] }}">
        <x-ui.icon name="globe" class="size-4 text-brand-600" />
        <span>{{ $current['name'] }}</span>
        <x-ui.icon name="chevron-down" class="size-4 text-muted transition-transform group-open:rotate-180" />
    </summary>

    <div class="absolute right-0 z-30 mt-2 max-h-80 w-56 overflow-y-auto rounded-xl border border-line bg-surface p-1.5 shadow-soft">
        <p class="px-3 pt-1.5 pb-2 text-xs font-semibold tracking-wider text-muted uppercase">{{ $label }}</p>
        <ul>
            @foreach ($versions as $version)
                <li>
                    <a href="{{ $version['url'] }}"
                       hreflang="{{ $version['code'] }}"
                       lang="{{ $version['code'] }}"
                       @class([
                           'flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                           'bg-brand-50 font-semibold text-brand-700' => $version['current'],
                           'text-body hover:bg-canvas hover:text-ink' => ! $version['current'],
                       ])
                       @if ($version['current']) aria-current="page" @endif>
                        <span>{{ $version['name'] }}</span>
                        @if ($version['current'])
                            <x-ui.icon name="check" class="size-4" stroke-width="2.5" />
                        @else
                            <span class="font-mono text-xs text-muted uppercase">{{ $version['code'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</details>
