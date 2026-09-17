@props(['steps'])

<ol {{ $attributes->class('grid gap-4 md:grid-cols-3') }}>
    @foreach ($steps as $step)
        <li class="card relative p-5">
            <span class="flex size-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white" aria-hidden="true">{{ $loop->iteration }}</span>
            <h3 class="mt-4 text-base font-semibold">{{ $step['title'] }}</h3>
            <p class="mt-1.5 text-sm leading-6 text-muted">{{ $step['text'] }}</p>
        </li>
    @endforeach
</ol>
