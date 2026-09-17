@props(['items' => []])

@if (count($items) > 1)
    <nav aria-label="Breadcrumb" {{ $attributes->class('text-sm text-muted') }}>
        <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1">
            @foreach ($items as $item)
                <li class="flex min-w-0 items-center gap-1.5">
                    @if (! $loop->first)
                        <x-ui.icon name="chevron-right" class="size-3.5 text-line-strong" />
                    @endif
                    @if ($loop->last)
                        <span aria-current="page" class="truncate text-body">{{ $item['label'] }}</span>
                    @else
                        <a href="{{ $item['url'] }}" class="hover:text-brand-700 hover:underline">{{ $item['label'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
