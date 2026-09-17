@props(['figure', 'priority' => false])

@if ($figure)
    <figure {{ $attributes->class('min-w-0') }}>
        <img
            src="{{ asset($figure['src']) }}"
            @if (count($figure['sources']) > 1)
                srcset="{{ collect($figure['sources'])->map(fn ($source) => asset($source['src']).' '.$source['width'].'w')->implode(', ') }}"
                sizes="(min-width: 1024px) 768px, 100vw"
            @endif
            width="{{ $figure['width'] }}"
            height="{{ $figure['height'] }}"
            alt="{{ $figure['alt'] }}"
            @if ($priority) fetchpriority="high" @else loading="lazy" @endif
            decoding="async"
            class="h-auto w-full rounded-xl border border-line bg-canvas"
        >
        <figcaption class="mt-3 text-sm leading-6 text-muted">{{ $figure['caption'] }}</figcaption>
    </figure>
@endif
