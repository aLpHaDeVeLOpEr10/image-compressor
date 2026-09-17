@props(['eyebrow' => null, 'title', 'description' => null, 'align' => 'center', 'id' => null])

<div {{ $attributes->class(['max-w-2xl', 'mx-auto text-center' => $align === 'center']) }}>
    @if ($eyebrow)
        <p class="eyebrow">{{ $eyebrow }}</p>
    @endif
    <h2 @if ($id) id="{{ $id }}" @endif class="section-title {{ $eyebrow ? 'mt-2' : '' }}">{{ $title }}</h2>
    @if ($description)
        <p class="mt-3 text-base leading-7 text-muted sm:text-lg">{{ $description }}</p>
    @endif
</div>
