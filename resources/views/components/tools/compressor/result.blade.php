@props(['text' => []])

@php
    $tab = 'rounded-md px-3 py-1.5 text-sm font-medium text-muted transition-colors hover:text-ink aria-selected:bg-surface aria-selected:text-ink aria-selected:shadow-card';
@endphp

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 rounded-xl border border-line bg-canvas p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div class="flex items-center gap-3">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-700 ring-1 ring-success-600/20" data-savings-icon>
                <x-ui.icon name="check" class="size-5" stroke-width="2.25" />
            </span>
            <div>
                <p class="text-lg font-bold text-ink focus:outline-none sm:text-xl" data-savings-headline tabindex="-1"></p>
                <p class="text-sm text-muted" data-savings-detail></p>
            </div>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="#" class="btn btn-primary btn-lg" data-download download>
                <x-ui.icon name="download" class="size-5" /> {{ $text['widget_download_button'] ?? '' }}
            </a>
        </div>
    </div>

    <ul class="space-y-2" data-notices></ul>

    <div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-base font-semibold text-ink">{{ $text['widget_compare_heading'] ?? '' }}</p>
            <div class="inline-flex rounded-lg bg-canvas p-1 ring-1 ring-line" role="tablist" aria-label="{{ $text['widget_compare_tabs_label'] ?? '' }}">
                <button type="button" role="tab" id="compare-tab-slider" aria-controls="compare-panel-slider" aria-selected="true" class="{{ $tab }}" data-compare-tab="slider">{{ $text['widget_tab_slider'] ?? '' }}</button>
                <button type="button" role="tab" id="compare-tab-side" aria-controls="compare-panel-side" aria-selected="false" tabindex="-1" class="{{ $tab }}" data-compare-tab="side">{{ $text['widget_tab_side'] ?? '' }}</button>
            </div>
        </div>

        <div id="compare-panel-slider" role="tabpanel" aria-labelledby="compare-tab-slider" class="mt-4" data-compare-panel="slider">
            <div class="checkerboard relative mx-auto aspect-[4/3] w-full overflow-hidden rounded-xl border border-line select-none" data-slider-frame>
                <img alt="{{ $text['widget_compressed_alt'] ?? '' }}" class="absolute inset-0 size-full object-contain" data-compare-after>
                <div class="absolute inset-0" style="clip-path: inset(0 50% 0 0)" data-compare-before-wrap>
                    <div class="checkerboard absolute inset-0"></div>
                    <img alt="{{ $text['widget_original_alt'] ?? '' }}" class="absolute inset-0 size-full object-contain" data-compare-before>
                </div>
                <div class="pointer-events-none absolute inset-y-0 left-1/2 w-0.5 -translate-x-1/2 bg-white shadow-[0_0_0_1px_rgb(15_23_42/0.2)]" data-compare-handle>
                    <span class="absolute top-1/2 left-1/2 flex size-9 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white text-ink shadow-soft ring-1 ring-line">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18-6-6 6-6M15 6l6 6-6 6" /></svg>
                    </span>
                </div>
                <span class="pointer-events-none absolute top-3 left-3 rounded-md bg-ink/75 px-2 py-1 text-xs font-medium text-white">{{ $text['widget_original'] ?? '' }}</span>
                <span class="pointer-events-none absolute top-3 right-3 rounded-md bg-ink/75 px-2 py-1 text-xs font-medium text-white">{{ $text['widget_compressed'] ?? '' }}</span>
                <input type="range" min="0" max="100" value="50" class="absolute inset-0 size-full cursor-ew-resize opacity-0" aria-label="{{ $text['widget_compare_range_label'] ?? '' }}" data-compare-range>
            </div>
            <p class="mt-2 text-center text-xs text-muted">{{ $text['widget_compare_hint'] ?? '' }}</p>
        </div>

        <div id="compare-panel-side" role="tabpanel" aria-labelledby="compare-tab-side" class="mt-4 grid gap-4 sm:grid-cols-2" data-compare-panel="side" hidden>
            <figure>
                <div class="checkerboard flex aspect-[4/3] items-center justify-center overflow-hidden rounded-xl border border-line">
                    <img alt="{{ $text['widget_original_alt'] ?? '' }}" class="max-h-full max-w-full object-contain" data-side-before>
                </div>
                <figcaption class="mt-2 flex justify-between text-sm"><span class="font-medium text-ink">{{ $text['widget_original'] ?? '' }}</span><span class="text-muted" data-side-before-size></span></figcaption>
            </figure>
            <figure>
                <div class="checkerboard flex aspect-[4/3] items-center justify-center overflow-hidden rounded-xl border border-line">
                    <img alt="{{ $text['widget_compressed_alt'] ?? '' }}" class="max-h-full max-w-full object-contain" data-side-after>
                </div>
                <figcaption class="mt-2 flex justify-between text-sm"><span class="font-medium text-ink">{{ $text['widget_compressed'] ?? '' }}</span><span class="text-muted" data-side-after-size></span></figcaption>
            </figure>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach (['original' => $text['widget_original'] ?? '', 'compressed' => $text['widget_compressed'] ?? ''] as $key => $label)
            <div class="rounded-xl border border-line p-4">
                <p class="text-sm font-semibold text-ink">{{ $label }}</p>
                <dl class="mt-3 grid grid-cols-3 gap-3 text-sm">
                    <div><dt class="text-xs text-muted">{{ $text['widget_size_label'] ?? '' }}</dt><dd class="mt-0.5 font-semibold text-ink" data-stat="{{ $key }}-size"></dd></div>
                    <div><dt class="text-xs text-muted">{{ $text['widget_dimensions_label'] ?? '' }}</dt><dd class="mt-0.5 font-semibold text-ink" data-stat="{{ $key }}-dimensions"></dd></div>
                    <div><dt class="text-xs text-muted">{{ $text['widget_format_label'] ?? '' }}</dt><dd class="mt-0.5 font-semibold text-ink" data-stat="{{ $key }}-format"></dd></div>
                </dl>
            </div>
        @endforeach
    </div>

    <div class="flex flex-col gap-2 border-t border-line pt-5 sm:flex-row sm:justify-between">
        <button type="button" class="btn btn-secondary" data-adjust>
            <x-ui.icon name="sliders" class="size-4" /> {{ $text['widget_adjust_button'] ?? '' }}
        </button>
        <button type="button" class="btn btn-secondary" data-reset>
            <x-ui.icon name="refresh" class="size-4" /> {{ $text['widget_another_button'] ?? '' }}
        </button>
    </div>
</div>
