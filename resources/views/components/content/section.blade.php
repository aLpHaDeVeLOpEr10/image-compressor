@props(['title', 'id' => null, 'muted' => false, 'narrow' => true])

<section @if ($id) id="{{ $id }}" @endif {{ $attributes->class(['section scroll-mt-16', 'bg-canvas border-y border-line' => $muted]) }}>
    <x-ui.container>
        <div @class(['max-w-3xl' => $narrow])>
            <h2 class="section-title">{{ $title }}</h2>
            <div class="prose-content mt-5">
                {{ $slot }}
            </div>
        </div>
    </x-ui.container>
</section>
