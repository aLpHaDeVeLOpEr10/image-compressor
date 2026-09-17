<x-layouts.admin title="Dashboard" description="Welcome back, {{ auth()->user()->name }}. Here's what's happening on {{ config('site.brand') }}.">
    <x-slot:actions>
        <x-ui.button :href="route('home')" variant="secondary" icon="external-link" target="_blank" rel="noopener">View website</x-ui.button>
        <x-ui.button :href="route('admin.messages.index', ['filter' => 'unread'])" icon="mail">Unread messages</x-ui.button>
    </x-slot:actions>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="card p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted">{{ $stat['label'] }}</p>
                    <span class="flex size-10 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-ui.icon :name="$stat['icon']" />
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold tracking-tight text-ink">{{ number_format($stat['value']) }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <x-admin.card title="Recent messages" description="The latest messages sent through the contact form." flush>
            <x-slot:actions>
                <x-ui.button :href="route('admin.messages.index')" variant="secondary" iconRight="arrow-right">View all</x-ui.button>
            </x-slot:actions>

            @forelse ($recentMessages as $message)
                @include('admin.messages.partials.row', ['message' => $message])
            @empty
                <div class="flex flex-col items-center px-6 py-14 text-center">
                    <span class="flex size-12 items-center justify-center rounded-full bg-canvas text-muted"><x-ui.icon name="mail" /></span>
                    <p class="mt-3 text-sm font-medium text-ink">No messages yet</p>
                    <p class="mt-1 text-sm text-muted">Messages from the contact form will appear here.</p>
                </div>
            @endforelse
        </x-admin.card>

        <x-admin.card title="Tools" description="Live tool pages, in menu order." flush>
            <x-slot:actions>
                <a href="{{ route('admin.tools.index') }}" class="link text-sm">View all</a>
            </x-slot:actions>

            <ul class="divide-y divide-line">
                @foreach ($tools as $tool)
                    <li>
                        <a href="{{ $tool->url() }}" target="_blank" rel="noopener" class="flex items-center gap-3 px-5 py-3.5 text-sm text-body hover:bg-canvas hover:text-ink sm:px-6">
                            <span class="flex size-8 items-center justify-center rounded-md bg-canvas text-muted"><x-ui.icon :name="$tool->icon" class="size-4" /></span>
                            <span class="min-w-0 flex-1 truncate font-medium">{{ $tool->name }}</span>
                            <x-ui.icon name="external-link" class="size-4 text-muted" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    </div>
</x-layouts.admin>
