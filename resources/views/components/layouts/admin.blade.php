@props(['title', 'description' => null, 'breadcrumbs' => [], 'actions' => null])

@php
    $user = auth()->user();
    $unreadCount = \App\Models\ContactMessage::unread()->count();
    $initials = collect(explode(' ', trim($user->name)))->filter()->take(2)->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $navigation = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'layout-grid'],
        ['label' => 'Tools', 'active' => 'admin.tools.*', 'icon' => 'layers', 'children' => [
            ['label' => 'All tools', 'route' => 'admin.tools.index', 'active' => ['admin.tools.index', 'admin.tools.edit', 'admin.tools.children.*'], 'icon' => 'layout-grid'],
            ['label' => 'Add tool', 'route' => 'admin.tools.create', 'active' => 'admin.tools.create', 'icon' => 'plus'],
            ['label' => 'Trash', 'route' => 'admin.tools.trash', 'active' => 'admin.tools.trash', 'icon' => 'trash', 'badge' => \App\Models\Tool::trashListing()->count(), 'badgeMuted' => true],
        ]],
        ['label' => 'Messages', 'route' => 'admin.messages.index', 'active' => 'admin.messages.*', 'icon' => 'mail', 'badge' => $unreadCount],
    ];
    $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ...$breadcrumbs];
@endphp

<!DOCTYPE html>
<html lang="{{ config('site.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · Admin | {{ config('site.brand') }}</title>

    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="group/admin min-h-screen bg-canvas" data-sidebar="expanded">
    <a href="#main" class="sr-only z-50 rounded-md bg-brand-600 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

    <div class="fixed inset-0 z-40 hidden bg-ink/40 group-data-[mobile=open]/admin:block lg:group-data-[mobile=open]/admin:hidden" data-sidebar-close></div>

    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-line bg-surface transition-transform duration-200 group-data-[mobile=open]/admin:translate-x-0 lg:translate-x-0 lg:group-data-[sidebar=collapsed]/admin:-translate-x-full">
        <div class="flex h-16 shrink-0 items-center border-b border-line px-5">
            <x-site.logo />
        </div>

        <nav aria-label="Admin" class="flex-1 overflow-y-auto px-3 py-6">
            <p class="px-3 text-xs font-semibold tracking-wider text-muted uppercase">Navigation</p>
            <ul class="mt-3 space-y-1">
                @foreach ($navigation as $item)
                    @php($isActive = request()->routeIs($item['active']))
                    @if (! empty($item['children']))
                        <li>
                            <details class="group/nav" @if ($isActive) open @endif>
                                <summary @class([
                                    'flex cursor-pointer list-none items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors [&::-webkit-details-marker]:hidden',
                                    'text-brand-700' => $isActive,
                                    'text-body hover:bg-canvas hover:text-ink' => ! $isActive,
                                ])>
                                    <x-ui.icon :name="$item['icon']" />
                                    {{ $item['label'] }}
                                    <x-ui.icon name="chevron-down" class="ml-auto size-4 text-muted transition-transform group-open/nav:rotate-180" />
                                </summary>
                                <ul class="mt-1 ml-5 space-y-1 border-l border-line pl-3">
                                    @foreach ($item['children'] as $child)
                                        @php($isChildActive = request()->routeIs(...(array) $child['active']))
                                        <li>
                                            <a href="{{ route($child['route']) }}"
                                               @class([
                                                   'flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                                   'bg-brand-50 text-brand-700 shadow-[inset_3px_0_0_var(--color-brand-600)]' => $isChildActive,
                                                   'text-body hover:bg-canvas hover:text-ink' => ! $isChildActive,
                                               ])
                                               @if ($isChildActive) aria-current="page" @endif>
                                                <x-ui.icon :name="$child['icon']" class="size-4" />
                                                {{ $child['label'] }}
                                                @if (! empty($child['badge']))
                                                    <span @class([
                                                        'ml-auto rounded-full px-2 py-0.5 text-xs font-semibold',
                                                        'bg-line text-body' => $child['badgeMuted'] ?? false,
                                                        'bg-brand-600 text-white' => ! ($child['badgeMuted'] ?? false),
                                                    ])>{{ $child['badge'] }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                        @continue
                    @endif
                    <li>
                        <a href="{{ route($item['route']) }}"
                           @class([
                               'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                               'bg-brand-50 text-brand-700 shadow-[inset_3px_0_0_var(--color-brand-600)]' => $isActive,
                               'text-body hover:bg-canvas hover:text-ink' => ! $isActive,
                           ])
                           @if ($isActive) aria-current="page" @endif>
                            <x-ui.icon :name="$item['icon']" />
                            {{ $item['label'] }}
                            @if (! empty($item['badge']))
                                <span class="ml-auto rounded-full bg-brand-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="border-t border-line px-3 py-5">
            <p class="px-3 text-xs font-semibold tracking-wider text-muted uppercase">Shortcuts</p>
            <ul class="mt-3 space-y-1">
                <li>
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-body hover:bg-canvas hover:text-ink">
                        <x-ui.icon name="external-link" />
                        View website
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-body hover:bg-canvas hover:text-ink">
                            <x-ui.icon name="log-out" />
                            Log out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </aside>

    <div class="transition-[padding] duration-200 lg:pl-64 lg:group-data-[sidebar=collapsed]/admin:pl-0">
        <header class="sticky top-0 z-30 border-b border-line bg-surface/95 backdrop-blur">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button type="button" class="flex size-10 shrink-0 items-center justify-center rounded-lg border border-line text-body hover:bg-canvas hover:text-ink" aria-controls="admin-sidebar" aria-label="Toggle sidebar" data-sidebar-toggle>
                    <x-ui.icon name="panel-left" />
                </button>

                <form method="GET" action="{{ route('admin.messages.index') }}" role="search" class="relative hidden w-full max-w-md sm:block">
                    <label for="admin-search" class="sr-only">Search messages</label>
                    <x-ui.icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4.5 -translate-y-1/2 text-muted" />
                    <input id="admin-search" name="search" type="search" value="{{ request()->routeIs('admin.messages.index') ? request('search') : '' }}" placeholder="Search messages by name, email or subject…" class="form-input pl-10">
                </form>

                <div class="ml-auto flex items-center gap-3">
                    <a href="{{ route('admin.messages.index', ['filter' => 'unread']) }}" class="relative flex size-10 items-center justify-center rounded-lg border border-line text-body hover:bg-canvas hover:text-ink" aria-label="Unread messages: {{ $unreadCount }}">
                        <x-ui.icon name="bell" />
                        @if ($unreadCount > 0)
                            <span class="absolute -top-1.5 -right-1.5 flex min-w-5 items-center justify-center rounded-full bg-brand-600 px-1 text-[0.6875rem] leading-5 font-semibold text-white ring-2 ring-surface">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </a>

                    <details class="relative" data-user-menu>
                        <summary class="flex cursor-pointer list-none items-center gap-3 rounded-lg border border-line py-1.5 pr-3 pl-1.5 hover:bg-canvas [&::-webkit-details-marker]:hidden">
                            <span class="flex size-8 items-center justify-center rounded-md bg-ink text-xs font-bold text-white">{{ $initials }}</span>
                            <span class="hidden text-left leading-tight sm:block">
                                <span class="block text-sm font-semibold text-ink">{{ $user->name }}</span>
                                <span class="block text-xs text-muted">Administrator</span>
                            </span>
                            <x-ui.icon name="chevron-down" class="size-4 text-muted" />
                        </summary>
                        <div class="absolute right-0 mt-2 w-56 rounded-xl border border-line bg-surface p-1.5 shadow-soft">
                            <p class="truncate px-3 py-2 text-xs text-muted">{{ $user->email }}</p>
                            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-body hover:bg-canvas hover:text-ink">
                                <x-ui.icon name="external-link" class="size-4" /> View website
                            </a>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-body hover:bg-canvas hover:text-ink">
                                    <x-ui.icon name="log-out" class="size-4" /> Log out
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            </div>
        </header>

        <main id="main" class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-3" />
                    <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $title }}</h1>
                    @if ($description)
                        <p class="mt-1.5 text-sm text-muted sm:text-base">{{ $description }}</p>
                    @endif
                </div>
                @if ($actions)
                    <div class="flex shrink-0 flex-wrap gap-3">{{ $actions }}</div>
                @endif
            </div>

            @if (session('status'))
                <x-ui.alert type="success" class="mt-6">{{ session('status') }}</x-ui.alert>
            @endif

            <div class="mt-6">
                {{ $slot }}
            </div>
        </main>
    </div>

    <script>
        (() => {
            const body = document.body;
            const desktop = window.matchMedia('(min-width: 64rem)');

            try {
                if (localStorage.getItem('admin-sidebar') === 'collapsed') {
                    body.dataset.sidebar = 'collapsed';
                }
            } catch {}

            document.querySelector('[data-sidebar-toggle]').addEventListener('click', () => {
                if (desktop.matches) {
                    body.dataset.sidebar = body.dataset.sidebar === 'collapsed' ? 'expanded' : 'collapsed';
                    try { localStorage.setItem('admin-sidebar', body.dataset.sidebar); } catch {}
                } else {
                    body.dataset.mobile = body.dataset.mobile === 'open' ? 'closed' : 'open';
                }
            });

            document.querySelector('[data-sidebar-close]').addEventListener('click', () => body.dataset.mobile = 'closed');

            document.addEventListener('click', (event) => {
                document.querySelectorAll('[data-user-menu][open]').forEach((menu) => {
                    if (! menu.contains(event.target)) {
                        menu.removeAttribute('open');
                    }
                });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
