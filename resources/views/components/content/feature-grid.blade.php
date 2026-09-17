@props(['items', 'columns' => 'lg:grid-cols-4'])

<ul {{ $attributes->class("grid gap-4 sm:grid-cols-2 {$columns}") }}>
    @foreach ($items as $item)
        <li class="card p-5">
            <span class="flex size-10 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                <x-ui.icon :name="$item['icon']" class="size-5" />
            </span>
            <h3 class="mt-4 text-base font-semibold">{{ $item['title'] }}</h3>
            <p class="mt-1.5 text-sm leading-6 text-muted">{{ $item['text'] }}</p>
        </li>
    @endforeach
</ul>
