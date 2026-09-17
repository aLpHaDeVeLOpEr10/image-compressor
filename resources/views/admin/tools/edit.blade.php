@php
    $fieldRows = old('fields', $fields);
    $currentPath = parse_url($tool->url(), PHP_URL_PATH) ?: '/';
    $languageName = $isChild ? ($languages[$record->locale] ?? strtoupper($record->locale)) : null;
    $breadcrumbs = [['label' => 'Tools', 'url' => route('admin.tools.index')]];

    if ($isChild) {
        $breadcrumbs[] = ['label' => $record->parent->name, 'url' => route('admin.tools.edit', $record->parent->key)];
    }

    $breadcrumbs[] = ['label' => $record->name, 'url' => route('admin.tools.edit', $record->key)];
@endphp

<x-layouts.admin
    :title="'Edit '.$record->name"
    :description="$isChild ? $languageName.' version of '.$record->parent->name.' · '.$currentPath : $currentPath"
    :breadcrumbs="$breadcrumbs"
>
    <x-slot:actions>
        @if ($tool->isPublished())
            <x-ui.button :href="$tool->url()" variant="secondary" icon="external-link" target="_blank" rel="noopener">View page</x-ui.button>
        @endif
        @if ($isChild)
            <x-ui.button :href="route('admin.tools.edit', $record->parent->key)" variant="secondary" icon="chevron-left">Back to {{ $record->parent->name }}</x-ui.button>
        @else
            <x-ui.button :href="route('admin.tools.children.create', $record->key)" icon="globe">Create child tool</x-ui.button>
            <x-ui.button :href="route('admin.tools.index')" variant="secondary" icon="chevron-left">Back to tools</x-ui.button>
        @endif
    </x-slot:actions>

    @if ($errors->any())
        <x-ui.alert type="error" class="mb-6">Please correct the highlighted fields and save again.</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.tools.update', $record->key) }}" enctype="multipart/form-data" class="space-y-6" data-tool-editor novalidate>
        @csrf
        @method('PUT')

        <x-admin.card title="Page Settings" description="The address, status and search engine details of this page." flush>
            <div class="px-5 py-5 sm:px-6">
                <x-admin.tool-settings
                    :values="$record->only(['name', 'slug', 'status', 'meta_title', 'meta_description'])"
                    :slug-prefix="$isChild ? '/'.$record->locale.'/' : '/'.config('tools.prefix').'/'"
                    :slug-locked="$isPrimary"
                    :slug-help="match (true) {
                        $isPrimary => 'The homepage tool is always served at the site root.',
                        $isChild && $isHomepage => 'Leave blank to serve this version at /'.$record->locale.'. Lowercase letters, numbers and dashes only.',
                        default => 'Lowercase letters, numbers and dashes only. Leave blank to generate it from the title. Changing it changes the page URL, so old links stop working.',
                    }"
                    :status-locked="$isPrimary"
                    :statuses="$statuses"
                    :current-image="$record->og_image"
                >
                    <x-slot:info>
                        <div>
                            <p class="form-label">Blade file</p>
                            <p class="flex items-center gap-2 rounded-lg border border-line bg-canvas px-3.5 py-2.5 text-xs text-body">
                                <x-ui.icon name="file" class="size-4 shrink-0 text-muted" /> <code class="font-mono break-all">{{ $bladeFile }}</code>
                            </p>
                            @if ($isChild)
                                <p class="mt-1.5 text-xs text-muted">Language versions use their parent's Blade file.</p>
                            @endif
                        </div>

                        @if ($isChild)
                            <div>
                                <p class="form-label">Language</p>
                                <p class="flex items-center gap-2 rounded-lg border border-line bg-canvas px-3.5 py-2.5 text-sm text-body">
                                    <x-ui.icon name="globe" class="size-4 text-muted" /> {{ $languageName }} <code class="ml-auto font-mono text-xs text-muted">{{ $record->locale }}</code>
                                </p>
                            </div>
                        @endif
                    </x-slot:info>
                </x-admin.tool-settings>
            </div>
        </x-admin.card>

        @unless ($isChild)
            <x-admin.card title="Languages" description="Child tools are translated copies of this tool.">
                <x-slot:actions>
                    <x-ui.button :href="route('admin.tools.children.create', $record->key)" variant="secondary" icon="plus" class="px-3 py-2">Add language</x-ui.button>
                </x-slot:actions>

                @if ($translations->isEmpty())
                    <p class="rounded-xl border border-dashed border-line-strong px-4 py-6 text-center text-sm text-muted">
                        No language versions yet. Add one to translate this page.
                    </p>
                @else
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($translations as $translation)
                            <a href="{{ route('admin.tools.edit', $translation->key) }}" class="group flex items-center gap-3 rounded-xl border border-line bg-canvas/60 px-4 py-3 transition-colors hover:border-brand-300 hover:bg-brand-50">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-surface font-mono text-xs font-bold text-brand-700 uppercase ring-1 ring-line">{{ $translation->locale }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="truncate text-sm font-semibold text-ink">{{ $languages[$translation->locale] ?? strtoupper($translation->locale) }}</span>
                                        @unless ($translation->isPublished())
                                            <span class="rounded-full bg-warning-50 px-2 py-0.5 text-[0.6875rem] font-medium text-warning-800">Draft</span>
                                        @endunless
                                    </span>
                                    <span class="block truncate text-xs text-muted">{{ $translation->name }} · {{ parse_url($translation->url(), PHP_URL_PATH) }}</span>
                                </span>
                                <x-ui.icon name="pencil" class="size-4 shrink-0 text-muted group-hover:text-brand-700" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-admin.card>
        @endunless

        <x-admin.card title="Content" description="Manage page content as key/value pairs. Developers reference keys in Blade templates.">
            <x-slot:actions>
                <button type="button" class="btn btn-secondary px-3 py-2" aria-expanded="false" aria-controls="deep-edit" data-deep-edit-toggle>
                    <x-ui.icon name="settings" class="size-4" /> Deep Edit
                </button>
            </x-slot:actions>

            <div id="deep-edit" class="mb-6 hidden rounded-xl border border-line bg-canvas/70 p-5" data-deep-edit>
                <p class="text-xs font-bold tracking-wider text-muted uppercase">Export</p>
                <p class="mt-1 text-sm text-body">Download the current content, including unsaved edits in the form, as JSON.</p>
                <x-ui.button variant="secondary" icon="download" class="mt-3 px-3 py-2" data-json-download>Download JSON</x-ui.button>

                <div class="my-5 border-t border-line"></div>

                <p class="text-xs font-bold tracking-wider text-muted uppercase">Import</p>
                <p class="mt-1 text-sm leading-6 text-body">
                    Paste JSON below. Accepts either a flat <code class="font-mono text-xs text-danger-700">{"key":"value"}</code> object or an array of
                    <code class="font-mono text-xs text-danger-700">{key, value, type}</code> entries. <strong>Merge</strong> updates matching keys and appends new ones.
                    <strong>Replace</strong> overwrites all content. Nothing is saved until you click Save changes.
                </p>
                <label for="json-import" class="sr-only">JSON to import</label>
                <textarea id="json-import" rows="6" spellcheck="false" placeholder='{"h1":"Compress images online","intro":"Shrink JPG, PNG and WebP files."}' class="form-input mt-3 bg-surface font-mono text-xs" data-json-input></textarea>
                <p class="mt-2 hidden text-sm" role="status" data-json-message></p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" class="btn bg-ink px-4 py-2 text-white hover:bg-ink/90" data-json-merge>Merge JSON</button>
                    <button type="button" class="btn border border-danger-200 bg-surface px-4 py-2 text-danger-700 hover:bg-danger-50" data-json-replace>Replace JSON</button>
                </div>
            </div>

            @if ($missingDefaultCount > 0)
                <div class="mb-5 flex flex-col gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-brand-800">
                        {{ $missingDefaultCount }} default {{ Str::plural('key', $missingDefaultCount) }} for this page {{ $missingDefaultCount === 1 ? 'is' : 'are' }} not listed below. The page shows the built-in text for them until they are added. Save any edits first.
                    </p>
                    <button type="submit" form="sync-defaults-form" class="btn btn-primary shrink-0 px-3 py-2">
                        <x-ui.icon name="plus" class="size-4" /> Add missing keys
                    </button>
                </div>
            @endif

            @error('fields')
                <x-ui.alert type="error" class="mb-4">{{ $message }}</x-ui.alert>
            @enderror

            <div class="space-y-3" data-field-list>
                @foreach ($fieldRows as $index => $field)
                    @include('admin.tools.partials.field-row', ['field' => $field, 'index' => $index])
                @endforeach
            </div>

            <p @class(['rounded-xl border border-dashed border-line-strong px-4 py-8 text-center text-sm text-muted', 'hidden' => count($fieldRows) > 0]) data-field-empty>
                No content fields yet. Add one to start managing this page's content.
            </p>

            <button type="button" class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-line-strong px-4 py-3 text-sm font-semibold text-body transition-colors hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700" data-field-add>
                <x-ui.icon name="plus" class="size-4" /> Add field
            </button>

            <details class="mt-4 rounded-xl border border-line bg-canvas/60 px-4 py-3 text-xs leading-5 text-muted">
                <summary class="cursor-pointer font-semibold text-body">How keys and values work</summary>
                <ul class="mt-2 list-disc space-y-1.5 pl-5">
                    <li><code class="font-mono">h1</code>, <code class="font-mono">intro</code>, <code class="font-mono">summary</code> and <code class="font-mono">card_description</code> set the page heading, introduction, summary and tool card text.</li>
                    <li>Numbered keys form lists, such as <code class="font-mono">feature_1</code>, <code class="font-mono">how_to_step_1_title</code> or <code class="font-mono">faq_1_question</code> with <code class="font-mono">faq_1_answer</code>. Add the next number to add an item, or clear the first value of an item to hide it.</li>
                    <li>Removing a key brings back its built-in text. To hide text, keep the key and clear its value.</li>
                    @if ($isHomepage)
                        <li>Keys starting with <code class="font-mono">site_</code> are the header, footer and navigation, and appear on every page.</li>
                    @endif
                    <li>Keys starting with <code class="font-mono">widget_msg_</code> are messages shown while compressing. Words like <code class="font-mono">:size</code> are filled in automatically.</li>
                    <li>Placeholders: <code class="font-mono">{brand}</code>, <code class="font-mono">{year}</code>, <code class="font-mono">{operator}</code>, <code class="font-mono">{max_upload_mb}</code>, <code class="font-mono">{max_megapixels}</code>, <code class="font-mono">{server_max_megapixels}</code>, and links such as <code class="font-mono">{url:home}</code>, <code class="font-mono">{url:pages.faq}</code> or <code class="font-mono">{url:tools.png-to-jpg}</code>.</li>
                    <li>Every key is available in the tool's Blade views as <code class="font-mono">$content</code>.</li>
                </ul>
            </details>

            <template data-field-template>
                @include('admin.tools.partials.field-row', ['field' => ['key' => '', 'type' => 'text', 'value' => ''], 'index' => null])
            </template>
        </x-admin.card>

        <div class="sticky bottom-4 z-20 flex flex-col gap-3 rounded-xl border border-line bg-surface/95 px-5 py-3.5 shadow-soft backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p class="text-sm text-muted">Changes to settings and content are saved together.</p>
            <div class="flex items-center justify-end gap-3">
                <x-ui.button :href="$isChild ? route('admin.tools.edit', $record->parent->key) : route('admin.tools.index')" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">Save changes</x-ui.button>
            </div>
        </div>

        @unless ($isPrimary)
            <x-admin.card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold tracking-tight text-ink">{{ $isChild ? 'Move this language version to the trash' : 'Move this tool to the trash' }}</h2>
                        <p class="mt-0.5 text-sm text-muted">
                            @if ($isChild)
                                The {{ $languageName }} page is hidden from the website. The {{ $record->parent->name }} page is not affected.
                            @else
                                The page and all of its language versions are hidden from the website.
                            @endif
                            You can restore it from <a href="{{ route('admin.tools.trash') }}" class="link">Trash</a>.
                        </p>
                    </div>
                    <button type="submit" form="delete-tool-form" class="btn shrink-0 border border-danger-200 bg-surface text-danger-700 hover:bg-danger-50">
                        <x-ui.icon name="trash" class="size-4" /> Move to trash
                    </button>
                </div>
            </x-admin.card>
        @endunless
    </form>

    @unless ($isPrimary)
        <form id="delete-tool-form" method="POST" action="{{ route('admin.tools.destroy', $record->key) }}" class="hidden" onsubmit="return confirm('Move {{ $isChild ? 'this language version' : 'this tool and its language versions' }} to the trash?')">
            @csrf
            @method('DELETE')
        </form>
    @endunless

    <form id="sync-defaults-form" method="POST" action="{{ route('admin.tools.sync-defaults', $record->key) }}" class="hidden">
        @csrf
    </form>

    @push('scripts')
        @vite('resources/js/admin/tool-editor.js')
    @endpush
</x-layouts.admin>
