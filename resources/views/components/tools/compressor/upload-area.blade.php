@props(['text' => [], 'accept' => '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp', 'formats' => ''])

<div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-line-strong bg-canvas px-4 py-10 text-center transition-colors data-[dragging]:border-brand-500 data-[dragging]:bg-brand-50 sm:py-14" data-dropzone>
    <span class="flex size-14 items-center justify-center rounded-full bg-surface text-brand-600 shadow-card ring-1 ring-line">
        <x-ui.icon name="upload" class="size-6" />
    </span>

    <p class="mt-5 text-lg font-semibold text-ink sm:text-xl">{{ $text['widget_drop_heading'] ?? '' }}</p>
    <p class="mt-1 text-sm text-muted">{{ $text['widget_drop_or'] ?? '' }}</p>

    <label class="btn btn-primary btn-lg mt-3 cursor-pointer focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-brand-600">
        <x-ui.icon name="image" class="size-5" />
        {{ $text['widget_choose_button'] ?? '' }}
        <input type="file" class="sr-only" accept="{{ $accept }}" data-file-input aria-describedby="upload-help">
    </label>

    <div id="upload-help" class="mt-5 space-y-1 text-sm text-muted">
        <p><span class="font-medium text-body">{{ $text['widget_supported_label'] ?? '' }}</span> {{ $formats }}</p>
        <p><span class="font-medium text-body">{{ $text['widget_max_size_label'] ?? '' }}</span> {{ $text['widget_max_size_value'] ?? '' }}</p>
        <p class="hidden sm:block">{{ $text['widget_paste_hint'] ?? '' }}</p>
    </div>
</div>
