@props(['text' => []])

<div class="min-w-0">
    <div class="checkerboard flex aspect-[4/3] items-center justify-center overflow-hidden rounded-xl border border-line">
        <img data-preview-image alt="{{ $text['widget_preview_alt'] ?? '' }}" class="max-h-full max-w-full object-contain">
    </div>

    <dl class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="col-span-2 min-w-0 rounded-lg border border-line px-3 py-2.5 sm:col-span-4">
            <dt class="text-xs font-medium text-muted">{{ $text['widget_file_name_label'] ?? '' }}</dt>
            <dd class="mt-0.5 truncate text-sm font-semibold text-ink" data-preview-name></dd>
        </div>
        <div class="rounded-lg border border-line px-3 py-2.5">
            <dt class="text-xs font-medium text-muted">{{ $text['widget_dimensions_label'] ?? '' }}</dt>
            <dd class="mt-0.5 text-sm font-semibold text-ink" data-preview-dimensions></dd>
        </div>
        <div class="rounded-lg border border-line px-3 py-2.5">
            <dt class="text-xs font-medium text-muted">{{ $text['widget_file_size_label'] ?? '' }}</dt>
            <dd class="mt-0.5 text-sm font-semibold text-ink" data-preview-size></dd>
        </div>
        <div class="col-span-2 rounded-lg border border-line px-3 py-2.5">
            <dt class="text-xs font-medium text-muted">{{ $text['widget_format_label'] ?? '' }}</dt>
            <dd class="mt-0.5 text-sm font-semibold text-ink" data-preview-format></dd>
        </div>
    </dl>
</div>
