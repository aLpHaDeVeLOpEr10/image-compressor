<x-layouts.admin
    title="Tools"
    description="Every tool page on {{ config('site.brand') }}, with its URL, category and content."
    :breadcrumbs="[['label' => 'Tools', 'url' => route('admin.tools.index')]]"
>
    <x-slot:actions>
        <x-ui.button :href="route('home')" variant="secondary" icon="external-link" target="_blank" rel="noopener">View homepage tool</x-ui.button>
        <x-ui.button :href="route('admin.tools.create')" icon="plus">Add new tool</x-ui.button>
    </x-slot:actions>

    <x-admin.card flush>
        <div class="flex flex-col gap-3 border-b border-line px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <nav aria-label="Filter tools by category" class="flex flex-wrap gap-1 rounded-lg bg-canvas p-1">
                @foreach (['' => 'All'] + $categories as $key => $label)
                    @php($isActive = ($category ?? '') === $key)
                    <a href="{{ route('admin.tools.index', array_filter(['category' => $key, 'search' => $search])) }}"
                       @class([
                           'flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium transition-colors',
                           'bg-surface text-ink shadow-card' => $isActive,
                           'text-muted hover:text-ink' => ! $isActive,
                       ])
                       @if ($isActive) aria-current="page" @endif>
                        {{ $label }}
                        <span class="rounded-full bg-line px-1.5 text-xs text-body">{{ $key === '' ? $totalTools : ($categoryCounts[$key] ?? 0) }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('admin.tools.index') }}" class="relative sm:w-72" role="search">
                @if ($category)
                    <input type="hidden" name="category" value="{{ $category }}">
                @endif
                <label for="tool-search" class="sr-only">Search tools</label>
                <x-ui.icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted" />
                <input id="tool-search" name="search" type="search" value="{{ $search }}" placeholder="Search tools" class="form-input pl-10">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-canvas/60 text-xs font-bold tracking-wider text-muted uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 sm:px-6">Tool</th>
                        <th scope="col" class="px-5 py-3.5">Category</th>
                        <th scope="col" class="px-5 py-3.5">Status</th>
                        <th scope="col" class="px-5 py-3.5">Last updated</th>
                        <th scope="col" class="px-5 py-3.5 text-right sm:px-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($tools as $tool)
                        @include('admin.tools.partials.index-row', ['tool' => $tool, 'isChild' => false])
                        @foreach ($children->get($tool->key, collect()) as $child)
                            @include('admin.tools.partials.index-row', ['tool' => $child, 'isChild' => true])
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">
                                <p class="text-sm font-medium text-ink">No tools found</p>
                                <p class="mt-1 text-sm text-muted">Try a different category or search term.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-layouts.admin>
