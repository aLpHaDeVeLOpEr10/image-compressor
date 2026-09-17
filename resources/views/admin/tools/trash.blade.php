<x-layouts.admin
    title="Trash"
    description="Tools moved to the trash are hidden from the website. Restore them, or delete them permanently."
    :breadcrumbs="[
        ['label' => 'Tools', 'url' => route('admin.tools.index')],
        ['label' => 'Trash', 'url' => route('admin.tools.trash')],
    ]"
>
    <x-slot:actions>
        <x-ui.button :href="route('admin.tools.index')" variant="secondary" icon="chevron-left">Back to tools</x-ui.button>
    </x-slot:actions>

    @error('restore')
        <x-ui.alert type="error" class="mb-6">{{ $message }}</x-ui.alert>
    @enderror

    <x-admin.card flush>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-canvas/60 text-xs font-bold tracking-wider text-muted uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 sm:px-6">Tool</th>
                        <th scope="col" class="px-5 py-3.5">Type</th>
                        <th scope="col" class="px-5 py-3.5">Trashed</th>
                        <th scope="col" class="px-5 py-3.5 text-right sm:px-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($trashedTools as $trashedTool)
                        @php
                            $isBuiltIn = ! $trashedTool->isChild() && in_array($trashedTool->key, $builtInKeys, true);
                            $path = $trashedTool->isChild()
                                ? '/'.$trashedTool->locale.($trashedTool->slug ? '/'.$trashedTool->slug : '')
                                : ($trashedTool->key === config('tools.primary') ? '/' : '/'.$toolsPrefix.'/'.($trashedTool->slug ?? $trashedTool->key));
                        @endphp
                        <tr class="transition-colors hover:bg-canvas/60">
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3.5">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-canvas text-muted ring-1 ring-line">
                                        @if ($trashedTool->isChild())
                                            <span class="font-mono text-xs font-bold uppercase">{{ $trashedTool->locale }}</span>
                                        @else
                                            <x-ui.icon name="trash" class="size-4.5" />
                                        @endif
                                    </span>
                                    <div class="min-w-56">
                                        <p class="font-semibold text-ink">{{ $trashedTool->name }}</p>
                                        <p class="mt-0.5 text-muted line-through decoration-line-strong">{{ $path }}</p>
                                        @if ($trashedTool->trashed_children_count > 0)
                                            <p class="mt-1 text-xs text-muted">With {{ $trashedTool->trashed_children_count }} {{ Str::plural('language version', $trashedTool->trashed_children_count) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if ($trashedTool->isChild())
                                    <span class="rounded-full bg-canvas px-2.5 py-1 text-xs font-medium text-body ring-1 ring-line">{{ $languages[$trashedTool->locale] ?? strtoupper($trashedTool->locale) }} version of {{ $trashedTool->parent->name }}</span>
                                @elseif ($isBuiltIn)
                                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700">Built-in tool</span>
                                @else
                                    <span class="rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700">Added in admin</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-body">
                                <time datetime="{{ $trashedTool->deleted_at->toIso8601String() }}" title="{{ $trashedTool->deleted_at->toDayDateTimeString() }}">{{ $trashedTool->deleted_at->diffForHumans() }}</time>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.tools.restore', $trashedTool->key) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" variant="secondary" icon="refresh" class="px-3 py-1.5 whitespace-nowrap">Restore</x-ui.button>
                                    </form>
                                    @if ($isBuiltIn)
                                        <span class="inline-flex items-center px-2 text-xs text-muted" title="Built-in tools are defined in code and can only be restored.">Restore only</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.tools.force-destroy', $trashedTool->key) }}" onsubmit="return confirm('Delete {{ e(addslashes($trashedTool->name)) }} permanently? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="secondary" icon="trash" class="border-danger-200 px-3 py-1.5 whitespace-nowrap text-danger-700 hover:border-danger-200 hover:bg-danger-50">Delete permanently</x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-canvas text-muted"><x-ui.icon name="trash" /></span>
                                <p class="mt-3 text-sm font-medium text-ink">The trash is empty</p>
                                <p class="mt-1 text-sm text-muted">Tools you move to the trash appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-layouts.admin>
