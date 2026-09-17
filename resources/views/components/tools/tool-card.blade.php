@props(['tool', 'cta' => 'Open tool'])

<a href="{{ $tool->url() }}" {{ $attributes->class('group card flex h-full flex-col p-5 transition-colors hover:border-brand-300') }}>
    <span class="flex size-10 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
        <x-ui.icon :name="$tool->icon" class="size-5" />
    </span>
    <span class="mt-4 text-base font-semibold text-ink group-hover:text-brand-700">{{ $tool->name }}</span>
    <span class="mt-1.5 flex-1 text-sm leading-6 text-muted">{{ $tool->cardDescription }}</span>
    <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-brand-700">
        {{ $cta }} <x-ui.icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-0.5" />
    </span>
</a>
