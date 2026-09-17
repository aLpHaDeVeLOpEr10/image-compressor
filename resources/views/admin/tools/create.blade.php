@php
    $type = old('type', $selectedParent ? 'child' : 'parent');
@endphp

<x-layouts.admin
    title="Add new tool"
    description="Create a new tool page with its own Blade file, or a language version (child) of an existing tool."
    :breadcrumbs="[
        ['label' => 'Tools', 'url' => route('admin.tools.index')],
        ['label' => 'Add new tool', 'url' => route('admin.tools.create')],
    ]"
>
    <x-slot:actions>
        <x-ui.button :href="route('admin.tools.index')" variant="secondary" icon="chevron-left">Back to tools</x-ui.button>
    </x-slot:actions>

    @if ($errors->any())
        <x-ui.alert type="error" class="mb-6">Please correct the highlighted fields and try again.</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.tools.store') }}" enctype="multipart/form-data" class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]" data-tool-editor data-add-tool novalidate>
        @csrf

        <div class="space-y-6">
            <x-admin.card title="Tool type" description="A parent tool is a new page. A child tool is a translated copy of an existing tool.">
                <fieldset>
                    <legend class="sr-only">Tool type</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach (['parent' => ['Parent tool', 'A new tool page at /tools/{slug} with its own Blade file.', 'layers'], 'child' => ['Child tool', 'A language version of an existing tool. Its content keys are copied from the parent.', 'globe']] as $value => [$label, $help, $icon])
                            <label class="flex cursor-pointer gap-3 rounded-xl border border-line-strong bg-surface p-4 transition-colors hover:border-brand-300 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                <input type="radio" name="type" value="{{ $value }}" class="mt-1 size-4 accent-brand-600" @checked($type === $value) data-type-radio>
                                <span>
                                    <span class="flex items-center gap-2 text-sm font-semibold text-ink"><x-ui.icon :name="$icon" class="size-4 text-brand-600" /> {{ $label }}</span>
                                    <span class="mt-1 block text-sm text-muted">{{ $help }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('type')<p class="mt-2 text-sm text-danger-700">{{ $message }}</p>@enderror
                </fieldset>

                <div class="mt-5 grid gap-5 sm:grid-cols-2" data-show-for="child" @if ($type !== 'child') hidden @endif>
                    <div>
                        <label for="parent" class="form-label">Parent tool <span class="text-danger-700">*</span></label>
                        <select id="parent" name="parent" class="form-input" @error('parent') aria-invalid="true" @enderror data-parent-select>
                            <option value="">Select the parent tool</option>
                            @foreach ($parents as $parentTool)
                                <option value="{{ $parentTool->key }}" data-homepage="{{ $parentTool->key === $primaryKey ? '1' : '0' }}" @selected(old('parent', $selectedParent) === $parentTool->key)>{{ $parentTool->name }}</option>
                            @endforeach
                        </select>
                        @error('parent')<p class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="locale" class="form-label">Language <span class="text-danger-700">*</span></label>
                        <select id="locale" name="locale" class="form-input" @error('locale') aria-invalid="true" @enderror data-locale-select>
                            <option value="">Select a language</option>
                            @foreach ($languages as $code => $label)
                                <option value="{{ $code }}" @selected(old('locale') === $code)>{{ $label }} ({{ $code }})</option>
                            @endforeach
                        </select>
                        @error('locale')<p class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                        <p class="mt-1.5 text-xs text-muted">Languages the parent already has are disabled.</p>
                    </div>
                </div>

                <div class="mt-5" data-show-for="parent" @if ($type !== 'parent') hidden @endif>
                    <div>
                        <label for="blade_view" class="form-label">Blade file name <span class="text-danger-700">*</span></label>
                        <div class="flex">
                            <span class="hidden items-center rounded-l-lg border border-r-0 border-line-strong bg-canvas px-3 text-sm whitespace-nowrap text-muted sm:inline-flex">{{ $viewsDirectory }}/</span>
                            <input id="blade_view" name="blade_view" type="text" value="{{ old('blade_view') }}" maxlength="100" spellcheck="false" placeholder="my-new-tool" class="form-input font-mono sm:rounded-none" @error('blade_view') aria-invalid="true" @enderror aria-describedby="blade-view-help" data-blade-input>
                            <span class="hidden items-center rounded-r-lg border border-l-0 border-line-strong bg-canvas px-3 text-sm text-muted sm:inline-flex">.blade.php</span>
                        </div>
                        @error('blade_view')<p class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                        <p id="blade-view-help" class="mt-1.5 text-xs leading-5 text-muted">
                            Lowercase letters, numbers and dashes. Leave blank to use the slug. The file is created with a starter section you can edit; an existing file with this name is used as it is and never overwritten.
                        </p>
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="What happens next">
                <ul class="space-y-3 text-sm text-body">
                    <li class="flex gap-2.5" data-show-for="parent" @if ($type !== 'parent') hidden @endif><x-ui.icon name="file" class="mt-0.5 size-4 text-brand-600" /> The Blade file is created and the tool gets the compressor widget, heading, introduction, FAQ and related tools sections.</li>
                    <li class="flex gap-2.5" data-show-for="parent" @if ($type !== 'parent') hidden @endif><x-ui.icon name="clipboard" class="mt-0.5 size-4 text-brand-600" /> Starter content keys (h1, intro, summary, card_description, body_heading, body_html) are added, ready to edit.</li>
                    <li class="flex gap-2.5" data-show-for="child" @if ($type !== 'child') hidden @endif><x-ui.icon name="globe" class="mt-0.5 size-4 text-brand-600" /> Every content key and value of the parent is copied, ready to translate. The parent's Blade file is used.</li>
                    <li class="flex gap-2.5"><x-ui.icon name="info" class="mt-0.5 size-4 text-muted" /> You are taken to the editor, where you can add and change content keys. Use Draft until the page is ready.</li>
                </ul>
            </x-admin.card>
        </div>

        <div class="xl:sticky xl:top-22">
            <x-admin.card title="Page Settings" flush>
                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <x-admin.tool-settings
                        :values="['status' => \App\Models\Tool::STATUS_DRAFT]"
                        :slug-prefix="$type === 'child' ? '/'.(old('locale') ?: '{lang}').'/' : '/'.config('tools.prefix').'/'"
                        slug-help="Lowercase letters, numbers and dashes only. Leave blank to generate it from the title."
                        :statuses="$statuses"
                    />
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-line bg-canvas/60 px-5 py-4 sm:px-6">
                    <x-ui.button :href="route('admin.tools.index')" variant="secondary">Cancel</x-ui.button>
                    <x-ui.button type="submit" icon="plus">Add tool</x-ui.button>
                </div>
            </x-admin.card>
        </div>
    </form>

    @push('scripts')
        <script>
            (() => {
                const form = document.querySelector('[data-add-tool]');
                const usedLocales = @json($usedLocales);
                const toolsPrefix = @json('/'.config('tools.prefix').'/');
                const parentSelect = form.querySelector('[data-parent-select]');
                const localeSelect = form.querySelector('[data-locale-select]');
                const slugPrefix = form.querySelector('[data-slug-prefix]');
                const slugHelp = document.getElementById('slug-help');
                const slugInput = document.getElementById('slug');
                const bladeInput = form.querySelector('[data-blade-input]');
                const currentType = () => form.querySelector('[data-type-radio]:checked')?.value ?? 'parent';

                const sync = () => {
                    const type = currentType();
                    form.querySelectorAll('[data-show-for]').forEach((element) => {
                        element.hidden = element.dataset.showFor !== type;
                    });

                    const used = usedLocales[parentSelect.value] ?? [];
                    [...localeSelect.options].forEach((option) => {
                        option.disabled = option.value !== '' && used.includes(option.value);
                    });
                    if (localeSelect.selectedOptions[0]?.disabled) {
                        localeSelect.value = '';
                    }

                    const isHomepage = parentSelect.selectedOptions[0]?.dataset.homepage === '1';
                    const lang = localeSelect.value || '{lang}';
                    slugPrefix.textContent = type === 'child' ? `/${lang}/` : toolsPrefix;
                    slugHelp.textContent = type === 'child' && isHomepage
                        ? `Leave blank to serve this version at /${lang}, the homepage for that language. Lowercase letters, numbers and dashes only.`
                        : 'Lowercase letters, numbers and dashes only. Leave blank to generate it from the title.';
                    bladeInput.placeholder = slugInput.value.trim() || 'my-new-tool';
                };

                form.addEventListener('change', sync);
                slugInput.addEventListener('input', sync);
                sync();
            })();
        </script>
        @vite('resources/js/admin/tool-editor.js')
    @endpush
</x-layouts.admin>
