@props([
    'values' => [],
    'slugPrefix' => null,
    'slugLocked' => false,
    'slugHelp' => '',
    'statusLocked' => false,
    'statuses' => [],
    'currentImage' => null,
    'info' => null,
])

{{-- Fields lay out in rows when the card is wide (the edit page) and stack when it is narrow (a sidebar). --}}
<div class="@container">
    <div class="space-y-6">
        @if ($info)
            <div class="grid gap-5 @3xl:grid-cols-2">
                {{ $info }}
            </div>

            <div class="border-t border-line"></div>
        @endif

        <div class="grid gap-5 @3xl:grid-cols-3">
            <div>
                <label for="name" class="form-label">Title <span class="text-danger-700">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name', $values['name'] ?? '') }}" required maxlength="100" class="form-input" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                @error('name')<p id="name-error" class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="slug" class="form-label">Slug</label>
                @if ($slugLocked)
                    <input id="slug" type="text" value="/" disabled class="form-input cursor-not-allowed bg-canvas text-muted">
                @else
                    <div class="flex">
                        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-line-strong bg-canvas px-3 text-sm whitespace-nowrap text-muted" data-slug-prefix>{{ $slugPrefix }}</span>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $values['slug'] ?? '') }}" maxlength="100" spellcheck="false" class="form-input min-w-0 rounded-l-none" @error('slug') aria-invalid="true" @enderror aria-describedby="slug-help">
                    </div>
                    @error('slug')<p class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                @endif
                <p id="slug-help" class="mt-1.5 text-xs leading-5 text-muted">{{ $slugHelp }}</p>
            </div>

            <div>
                <label for="status" class="form-label">Status</label>
                @if ($statusLocked)
                    <input type="hidden" name="status" value="{{ \App\Models\Tool::STATUS_PUBLISHED }}">
                    <select id="status" disabled class="form-input cursor-not-allowed bg-canvas text-muted"><option>Published</option></select>
                    <p class="mt-1.5 text-xs text-muted">The homepage tool must stay published.</p>
                @else
                    <select id="status" name="status" class="form-input" @error('status') aria-invalid="true" @enderror>
                        @foreach ($statuses as $statusKey => $statusLabel)
                            <option value="{{ $statusKey }}" @selected(old('status', $values['status'] ?? \App\Models\Tool::STATUS_PUBLISHED) === $statusKey)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                    <p class="mt-1.5 text-xs text-muted">Draft pages return 404 and are hidden from menus and the sitemap.</p>
                @endif
            </div>
        </div>

        <div class="border-t border-line"></div>

        <div class="grid gap-5 @3xl:grid-cols-3">
            <div>
                <label for="meta_title" class="form-label">Meta Title <span class="text-danger-700">*</span></label>
                <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $values['meta_title'] ?? '') }}" required maxlength="120" class="form-input" @error('meta_title') aria-invalid="true" @enderror data-char-count="meta_title_count">
                @error('meta_title')<p class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                <p class="mt-1.5 flex justify-between gap-3 text-xs text-muted"><span>Recommended: 50–60 characters.</span><span id="meta_title_count" data-recommended="50-60"></span></p>
            </div>

            <div>
                <label for="meta_description" class="form-label">Meta Description <span class="text-danger-700">*</span></label>
                <textarea id="meta_description" name="meta_description" rows="4" required maxlength="300" class="form-input" @error('meta_description') aria-invalid="true" @enderror data-char-count="meta_description_count">{{ old('meta_description', $values['meta_description'] ?? '') }}</textarea>
                @error('meta_description')<p class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
                <p class="mt-1.5 flex justify-between gap-3 text-xs text-muted"><span>Recommended: 150–160 characters.</span><span id="meta_description_count" data-recommended="150-160"></span></p>
            </div>

            <div>
                <p class="form-label">Page Image (OG Image)</p>
                <div class="flex items-start gap-4 rounded-xl border border-dashed border-line-strong p-4">
                    <div class="flex h-20 w-28 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-line bg-canvas" data-image-preview>
                        @if ($currentImage)
                            <img src="{{ asset('storage/'.$currentImage) }}" alt="Current page image" class="size-full object-cover">
                        @else
                            <x-ui.icon name="image" class="text-muted" />
                        @endif
                    </div>
                    <div class="min-w-0 space-y-2">
                        <label class="btn btn-secondary cursor-pointer px-3 py-2">
                            <x-ui.icon name="upload" class="size-4" /> Choose file
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" data-image-input>
                        </label>
                        @if ($currentImage)
                            <label class="flex items-center gap-2 text-xs font-medium text-danger-700">
                                <input type="checkbox" name="remove_image" value="1" class="size-4 accent-danger-700" @checked(old('remove_image'))>
                                Remove current image
                            </label>
                        @endif
                        <p class="text-xs leading-5 text-muted">Recommended 1200×630px. JPG, PNG or WebP, max 2 MB.</p>
                    </div>
                </div>
                @error('image')<p class="mt-1.5 text-sm text-danger-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>
