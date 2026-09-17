<tr @class(['transition-colors hover:bg-canvas/60', 'bg-canvas/30' => $isChild])>
    <td class="px-5 py-4 sm:px-6">
        <div @class(['flex items-center gap-3.5', 'pl-8' => $isChild])>
            @if ($isChild)
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-canvas font-mono text-xs font-bold text-brand-700 uppercase ring-1 ring-line" title="{{ $languages[$tool->locale] ?? $tool->locale }}">{{ $tool->locale }}</span>
            @else
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                    <x-ui.icon :name="$tool->icon" class="size-4.5" />
                </span>
            @endif
            <div class="min-w-56">
                <p class="flex flex-wrap items-center gap-2 font-semibold text-ink">
                    {{ $tool->name }}
                    @if ($isChild)
                        <span class="rounded-full bg-canvas px-2 py-0.5 text-[0.6875rem] font-medium text-muted ring-1 ring-line">{{ $languages[$tool->locale] ?? strtoupper($tool->locale) }}</span>
                    @elseif ($tool->custom)
                        <span class="rounded-full bg-success-50 px-2 py-0.5 text-[0.6875rem] font-medium text-success-700">Added in admin</span>
                    @elseif ($tool->isPrimary())
                        <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[0.6875rem] font-medium text-brand-700">Homepage</span>
                    @elseif (in_array($tool->key, $navigationKeys, true))
                        <span class="rounded-full bg-canvas px-2 py-0.5 text-[0.6875rem] font-medium text-muted">In menu</span>
                    @endif
                </p>
                <p class="mt-0.5 text-body">{{ parse_url($tool->url(), PHP_URL_PATH) ?: '/' }}</p>
            </div>
        </div>
    </td>
    <td class="px-5 py-4 whitespace-nowrap text-body">{{ $categories[$tool->category] ?? $tool->category }}</td>
    <td class="px-5 py-4">
        @if ($tool->isPublished())
            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-3 py-1 text-xs font-semibold whitespace-nowrap text-success-700">
                <span class="size-1.5 rounded-full bg-success-600"></span>
                Published
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-3 py-1 text-xs font-semibold whitespace-nowrap text-warning-800">
                <span class="size-1.5 rounded-full bg-warning-800"></span>
                Draft
            </span>
        @endif
    </td>
    <td class="px-5 py-4 whitespace-nowrap text-body">
        <time datetime="{{ $tool->updatedAt->toDateString() }}">{{ $tool->updatedAt->format('j M Y') }}</time>
    </td>
    <td class="px-5 py-4 sm:px-6">
        <div class="flex justify-end gap-1">
            @if ($tool->isPublished())
                <a href="{{ $tool->url() }}" target="_blank" rel="noopener" class="flex size-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-brand-50 hover:text-brand-700" title="View page" aria-label="View {{ $tool->name }} page">
                    <x-ui.icon name="eye" class="size-4.5" />
                </a>
            @endif
            <a href="{{ route('admin.tools.edit', $tool->key) }}" class="flex size-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-brand-50 hover:text-brand-700" title="Edit" aria-label="Edit {{ $tool->name }}">
                <x-ui.icon name="pencil" class="size-4.5" />
            </a>
            @unless ($isChild)
                <a href="{{ route('admin.tools.children.create', $tool->key) }}" class="flex size-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-brand-50 hover:text-brand-700" title="Create child tool" aria-label="Create a language version of {{ $tool->name }}">
                    <x-ui.icon name="globe" class="size-4.5" />
                </a>
            @endunless
            @unless ($tool->isPrimary())
                <form method="POST" action="{{ route('admin.tools.destroy', $tool->key) }}" onsubmit="return confirm('Move {{ $isChild ? 'this language version' : 'this tool and its language versions' }} to the trash?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="flex size-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-danger-50 hover:text-danger-700" title="Move to trash" aria-label="Move {{ $tool->name }} to the trash">
                        <x-ui.icon name="trash" class="size-4.5" />
                    </button>
                </form>
            @endunless
        </div>
    </td>
</tr>
