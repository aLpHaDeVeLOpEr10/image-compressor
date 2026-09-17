@props(['title' => 'Short answer', 'updated' => null, 'updatedLabel' => 'Last updated'])

<aside {{ $attributes->class('rounded-xl border border-brand-100 bg-brand-50/60 p-5 sm:p-6') }} aria-label="{{ $title }}">
    <p class="text-sm font-semibold tracking-wide text-brand-800 uppercase">{{ $title }}</p>
    <p class="mt-2 text-base leading-7 text-ink">{{ $slot }}</p>
    @if ($updated)
        <p class="mt-3 text-xs text-muted">{{ $updatedLabel }} <time datetime="{{ $updated->toDateString() }}">{{ $updated->format('F j, Y') }}</time></p>
    @endif
</aside>
