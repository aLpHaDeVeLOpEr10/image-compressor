@props(['title' => null, 'description' => null, 'actions' => null, 'flush' => false])

<section {{ $attributes->class('card overflow-hidden') }}>
    @if ($title)
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line px-5 py-4 sm:px-6 sm:py-5">
            <div class="min-w-0">
                <h2 class="text-lg font-bold tracking-tight text-ink">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-sm text-muted">{{ $description }}</p>
                @endif
            </div>
            @if ($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endif
        </header>
    @endif

    <div @class(['px-5 py-5 sm:px-6' => ! $flush])>
        {{ $slot }}
    </div>
</section>
