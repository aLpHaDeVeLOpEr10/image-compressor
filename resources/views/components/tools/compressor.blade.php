@props(['options' => [], 'id' => 'compressor', 'text' => []])

@php
    $config = config('compressor');
    $inputFormats = array_values(array_intersect(array_keys($config['formats']), (array) ($options['input'] ?? array_keys($config['formats']))));
    $inputExtensions = array_merge(...array_map(fn (string $mime) => $config['formats'][$mime]['extensions'], $inputFormats));
    $inputAccept = implode(',', array_merge(array_map(fn (string $extension) => '.'.$extension, $inputExtensions), $inputFormats));
    $inputUploadLabel = implode(', ', array_map(fn (string $mime) => $config['formats'][$mime]['upload_label'], $inputFormats));
    $settings = [
        'input' => $inputFormats,
        'inputLabel' => implode(', ', array_map(fn (string $mime) => $config['formats'][$mime]['label'], $inputFormats)),
        'endpoint' => route('process.compress'),
        'maxBytes' => $config['max_upload_mb'] * 1024 * 1024,
        'maxPixels' => $config['max_pixels'],
        'serverMaxPixels' => $config['server_max_pixels'],
        'defaultQuality' => $options['quality'] ?? $config['default_quality'],
        'targetKb' => $options['target_kb'] ?? null,
        'output' => $options['output'] ?? 'original',
        'maxCustomTargetKb' => $config['max_custom_target_kb'],
        'formats' => collect($config['formats'])->map(fn (array $format) => $format['label'])->all(),
        'messages' => collect($text)->filter(fn (string $value, string $key) => str_starts_with($key, 'widget_msg_'))->mapWithKeys(fn (string $value, string $key) => [substr($key, 11) => $value])->all(),
    ];
@endphp

@push('scripts')
    @vite('resources/js/compressor/index.js')
@endpush

<section id="{{ $id }}" {{ $attributes->class('card scroll-mt-24 overflow-hidden shadow-soft') }} data-compressor data-settings='@json($settings)' aria-label="{{ $text['widget_aria_label'] ?? '' }}">
    <noscript>
        <x-ui.alert type="warning" class="m-5">{{ $text['widget_noscript'] ?? '' }}</x-ui.alert>
    </noscript>

    <div class="p-4 sm:p-6">
        <div data-error-region hidden class="mb-4">
            <x-ui.alert type="error"><span data-error-message></span></x-ui.alert>
        </div>

        <div data-stage="upload">
            <x-tools.compressor.upload-area :text="$text" :accept="$inputAccept" :formats="$inputUploadLabel" />
        </div>

        <div data-stage="configure" hidden>
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,22rem)]">
                <x-tools.compressor.image-preview :text="$text" />
                <x-tools.compressor.controls :settings="$settings" :text="$text" />
            </div>
        </div>

        <div data-stage="result" hidden>
            <x-tools.compressor.result :text="$text" />
        </div>

        <p class="sr-only" aria-live="polite" data-live-region></p>
    </div>

    <div class="flex items-start gap-2.5 border-t border-line bg-canvas px-4 py-3 text-xs leading-5 text-muted sm:px-6">
        <x-ui.icon name="lock" class="mt-0.5 size-4 text-success-700" />
        <p>
            {{ $text['widget_privacy_note'] ?? '' }}
            @if (($text['widget_privacy_link'] ?? '') !== '')
                <a href="{{ route('pages.privacy-policy') }}" class="link">{{ $text['widget_privacy_link'] ?? '' }}</a>
            @endif
        </p>
    </div>
</section>
