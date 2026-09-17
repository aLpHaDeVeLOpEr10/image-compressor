@props(['title', 'description' => null, 'breadcrumbs' => [], 'meta' => null])

<header {{ $attributes->class('border-b border-line bg-canvas') }}>
    <x-ui.container class="py-10 sm:py-14">
        <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-5" />
        <h1 class="max-w-3xl text-3xl font-bold tracking-tight sm:text-4xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-4 max-w-2xl text-base leading-7 text-muted sm:text-lg">{{ $description }}</p>
        @endif
        @if ($meta)
            <div class="mt-4 text-sm text-muted">{{ $meta }}</div>
        @endif
    </x-ui.container>
</header>
