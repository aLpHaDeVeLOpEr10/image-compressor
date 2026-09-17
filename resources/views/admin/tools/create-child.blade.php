<x-layouts.admin
    title="Create child tool"
    :description="'Add a language version of '.$parent->name.'. All '.$parent->contentFields->count().' content keys and values are copied from the parent, ready to translate.'"
    :breadcrumbs="[
        ['label' => 'Tools', 'url' => route('admin.tools.index')],
        ['label' => $parent->name, 'url' => route('admin.tools.edit', $parent->key)],
        ['label' => 'Create child tool', 'url' => route('admin.tools.children.create', $parent->key)],
    ]"
>
    <x-slot:actions>
        <x-ui.button :href="route('admin.tools.edit', $parent->key)" variant="secondary" icon="chevron-left">Back to {{ $parent->name }}</x-ui.button>
    </x-slot:actions>

    @if ($errors->any())
        <x-ui.alert type="error" class="mb-6">Please correct the highlighted fields and try again.</x-ui.alert>
    @endif

    @if ($languages === [])
        <x-ui.alert type="info">{{ $parent->name }} already has a version in every available language. Add more languages in <code>config/tools.php</code>.</x-ui.alert>
    @else
        <form method="POST" action="{{ route('admin.tools.children.store', $parent->key) }}" enctype="multipart/form-data" class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]" data-tool-editor novalidate>
            @csrf

            <x-admin.card title="Page Settings" description="The language, address and search engine details of the new page." flush>
                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <div>
                        <label for="locale" class="form-label">Language <span class="text-danger-700">*</span></label>
                        <select id="locale" name="locale" required class="form-input" @error('locale') aria-invalid="true" @enderror data-locale-select>
                            <option value="">Select a language</option>
                            @foreach ($languages as $code => $label)
                                <option value="{{ $code }}" @selected(old('locale') === $code)>{{ $label }} ({{ $code }})</option>
                            @endforeach
                        </select>
                        @error('locale')<p class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                    </div>

                    <x-admin.tool-settings
                        :values="$parent->only(['name', 'meta_title', 'meta_description']) + ['slug' => $isHomepage ? '' : $parent->slug, 'status' => \App\Models\Tool::STATUS_DRAFT]"
                        :slug-prefix="'/'.(old('locale') ?: '{lang}').'/'"
                        :slug-help="$isHomepage
                            ? 'Leave blank to serve this version at /{lang}, the homepage for that language. Lowercase letters, numbers and dashes only.'
                            : 'Use the translated name, e.g. compresor-de-imagenes. Leave blank to generate it from the title.'"
                        :statuses="$statuses"
                    />
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-line bg-canvas/60 px-5 py-4 sm:px-6">
                    <x-ui.button :href="route('admin.tools.edit', $parent->key)" variant="secondary">Cancel</x-ui.button>
                    <x-ui.button type="submit" icon="plus">Create child tool</x-ui.button>
                </div>
            </x-admin.card>

            <x-admin.card title="What gets copied">
                <ul class="space-y-3 text-sm text-body">
                    <li class="flex gap-2.5"><x-ui.icon name="check" class="mt-0.5 size-4 text-success-600" /> All {{ $parent->contentFields->count() }} content keys and their current values, in the same order.</li>
                    <li class="flex gap-2.5"><x-ui.icon name="check" class="mt-0.5 size-4 text-success-600" /> The same compressor widget, figures and layout as {{ $parent->name }}.</li>
                    <li class="flex gap-2.5"><x-ui.icon name="globe" class="mt-0.5 size-4 text-brand-600" /> After creating it, translate the values in its editor. Links between language versions (hreflang) and the sitemap are updated automatically.</li>
                    <li class="flex gap-2.5"><x-ui.icon name="info" class="mt-0.5 size-4 text-muted" /> New versions start as drafts so you can translate before publishing.</li>
                </ul>
            </x-admin.card>
        </form>
    @endif

    @push('scripts')
        <script>
            document.querySelector('[data-locale-select]')?.addEventListener('change', (event) => {
                const prefix = document.querySelector('[data-slug-prefix]');
                const help = document.getElementById('slug-help');
                const lang = event.target.value || '{lang}';
                if (prefix) prefix.textContent = `/${lang}/`;
                if (help) help.textContent = help.textContent.replace(/\/(\{lang\}|[a-z]{2})(?=[ ,.])/, `/${lang}`);
            });
        </script>
        @vite('resources/js/admin/tool-editor.js')
    @endpush
</x-layouts.admin>
