@props(['tools', 'current', 'label'])

@if ($tools->count() > 1)
    <nav {{ $attributes->class('flex flex-wrap items-center gap-2') }} aria-label="{{ $label }}">
        <span class="mr-1 text-sm font-semibold text-ink">{{ $label }}:</span>
        @foreach ($tools as $tool)
            @if ($tool->key === $current)
                <span aria-current="page" class="rounded-full border border-brand-600 bg-brand-50 px-3.5 py-2 text-sm font-medium text-brand-800">{{ $tool->name }}</span>
            @else
                <a href="{{ $tool->url() }}" class="rounded-full border border-line-strong bg-surface px-3.5 py-2 text-sm font-medium text-body hover:border-brand-300 hover:text-brand-700">{{ $tool->name }}</a>
            @endif
        @endforeach
    </nav>
@endif
