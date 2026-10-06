@php
    $registry = app(\App\Tools\ToolRegistry::class);
    $site = $registry->siteContent();
    $links = collect(\App\Tools\ToolContent::list($site, 'site_nav', ['label', 'tool']))
        ->map(fn (array $item) => [...$item, 'page' => $registry->localized($item['tool'], app()->getLocale())])
        ->filter(fn (array $item) => $item['page'] !== null)
        ->map(fn (array $item) => ['label' => $item['label'], 'url' => $item['page']->url(), 'active' => url()->current() === $item['page']->url()])
        ->values();
@endphp

<header class="relative z-40 border-b border-line bg-surface" data-site-header>
    <x-ui.container class="flex h-16 items-center justify-between gap-4">
        <x-site.logo />

        <nav aria-label="Main" class="hidden xl:block">
            <ul class="flex items-center gap-1">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ $link['url'] }}"
                           @class([
                               'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                               'text-brand-700 bg-brand-50' => $link['active'],
                               'text-body hover:text-ink hover:bg-canvas' => ! $link['active'],
                           ])
                           @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center gap-2">
            @if (($site['site_header_button'] ?? '') !== '')
                <x-ui.button :href="$site['site_header_button_url'] ?? route('pages.contact')" icon="mail" class="hidden sm:inline-flex">{{ $site['site_header_button'] }}</x-ui.button>
            @endif

            <button type="button" class="btn btn-ghost -mr-2 px-2.5 xl:hidden" aria-controls="mobile-menu" aria-expanded="false" data-menu-toggle data-open-label="{{ $site['site_menu_open'] ?? '' }}" data-close-label="{{ $site['site_menu_close'] ?? '' }}">
                <span class="sr-only" data-menu-label>{{ $site['site_menu_open'] ?? '' }}</span>
                <x-ui.icon name="menu" class="size-6" data-menu-open-icon />
                <x-ui.icon name="x" class="hidden size-6" data-menu-close-icon />
            </button>
        </div>
    </x-ui.container>

    <nav id="mobile-menu" aria-label="Mobile" class="border-t border-line bg-surface xl:hidden" hidden>
        <x-ui.container class="py-3">
            <ul class="grid gap-1">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ $link['url'] }}"
                           @class([
                               'block rounded-md px-3 py-2.5 text-base font-medium',
                               'bg-brand-50 text-brand-700' => $link['active'],
                               'text-body hover:bg-canvas' => ! $link['active'],
                           ])
                           @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                    </li>
                @endforeach
            </ul>
            @if (($site['site_header_button'] ?? '') !== '')
                <x-ui.button :href="$site['site_header_button_url'] ?? route('pages.contact')" icon="mail" class="mt-3 w-full sm:hidden">{{ $site['site_header_button'] }}</x-ui.button>
            @endif
        </x-ui.container>
    </nav>
</header>
