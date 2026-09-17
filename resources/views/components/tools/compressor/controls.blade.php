@props(['settings', 'text' => []])

@php
    $presets = config('compressor.target_presets');
    $outputs = array_intersect_key([
        'original' => $text['widget_output_original'] ?? '',
        'image/jpeg' => $text['widget_output_jpg'] ?? '',
        'image/webp' => $text['widget_output_webp'] ?? '',
    ], config('compressor.output_formats'));
    $customTargetKb = $settings['targetKb'] && ! array_key_exists((int) $settings['targetKb'], $presets) ? (int) $settings['targetKb'] : null;
    $pillLabel = 'flex cursor-pointer items-center justify-center rounded-lg border border-line-strong bg-surface px-3 py-2.5 text-sm font-medium text-body sm:py-2 transition-colors hover:border-brand-300 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-800 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brand-600';
@endphp

<form class="flex flex-col gap-6" data-controls novalidate>
    <div>
        <div class="flex items-center justify-between gap-3">
            <label for="quality-input" class="text-sm font-semibold text-ink">
                {{ $text['widget_quality_label'] ?? '' }} <span data-quality-value>{{ $settings['defaultQuality'] }}</span>%
            </label>
        </div>
        <input id="quality-input" type="range" name="quality" min="10" max="100" step="1" value="{{ $settings['defaultQuality'] }}" class="range mt-3" data-quality-input aria-describedby="quality-help">
        <div class="mt-1.5 flex justify-between text-xs text-muted" aria-hidden="true">
            <span>{{ $text['widget_quality_smaller'] ?? '' }}</span><span>{{ $text['widget_quality_higher'] ?? '' }}</span>
        </div>
        <p id="quality-help" class="mt-2 text-xs leading-5 text-muted" data-quality-help>
            {{ $text['widget_msg_quality_default'] ?? '' }}
        </p>
    </div>

    <fieldset>
        <legend class="text-sm font-semibold text-ink">{{ $text['widget_target_legend'] ?? '' }}</legend>
        <div class="mt-3 grid grid-cols-3 gap-2">
            <label class="{{ $pillLabel }}">
                <input type="radio" name="target" value="" class="sr-only" @checked(! $settings['targetKb'])>
                {{ $text['widget_no_target'] ?? '' }}
            </label>
            @foreach ($presets as $kb => $label)
                <label class="{{ $pillLabel }}">
                    <input type="radio" name="target" value="{{ $kb }}" class="sr-only" @checked((int) $settings['targetKb'] === $kb)>
                    {{ $label }}
                </label>
            @endforeach
            <label class="{{ $pillLabel }} col-span-3">
                <input type="radio" name="target" value="custom" class="sr-only" data-target-custom-radio @checked($customTargetKb)>
                {{ $text['widget_custom_size'] ?? '' }}
            </label>
        </div>

        <div class="mt-3" data-custom-target hidden>
            <label for="custom-target-input" class="form-label">{{ $text['widget_custom_target_label'] ?? '' }}</label>
            <input id="custom-target-input" type="number" name="custom_target" @if ($customTargetKb) value="{{ $customTargetKb }}" @endif min="5" max="{{ $settings['maxCustomTargetKb'] }}" step="1" inputmode="numeric" placeholder="{{ $text['widget_custom_target_placeholder'] ?? '' }}" class="form-input" data-custom-target-input aria-describedby="custom-target-error">
            <p id="custom-target-error" class="mt-1.5 text-xs text-danger-700" data-custom-target-error hidden></p>
        </div>

        <p class="mt-3 text-xs leading-5 text-muted" data-target-summary aria-live="polite"></p>
    </fieldset>

    <fieldset>
        <legend class="text-sm font-semibold text-ink">{{ $text['widget_output_legend'] ?? '' }}</legend>
        <div class="mt-3 grid grid-cols-3 gap-2">
            @foreach ($outputs as $value => $label)
                <label class="{{ $pillLabel }} text-center">
                    <input type="radio" name="output" value="{{ $value }}" class="sr-only" @checked($settings['output'] === $value)>
                    <span @if ($value === 'original') data-original-format-label @endif>{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <p class="mt-3 text-xs leading-5 text-muted" data-output-help></p>
    </fieldset>

    <div class="mt-auto flex flex-col gap-2 border-t border-line pt-5">
        <button type="submit" class="btn btn-primary btn-lg w-full" data-compress-button>
            <svg class="hidden size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true" data-spinner>
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3" />
                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
            </svg>
            <span data-compress-label>{{ $text['widget_compress_button'] ?? '' }}</span>
        </button>
        <p class="min-h-5 text-center text-xs text-muted" data-status></p>
        <button type="button" class="btn btn-ghost w-full" data-reset>
            <x-ui.icon name="x" class="size-4" /> {{ $text['widget_choose_different'] ?? '' }}
        </button>
    </div>
</form>
