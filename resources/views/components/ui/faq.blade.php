@props(['faqs' => [], 'headingLevel' => 3])

<div {{ $attributes->class('divide-y divide-line rounded-xl border border-line bg-surface') }}>
    @foreach ($faqs as $faq)
        <details class="group">
            <summary class="flex cursor-pointer list-none items-start justify-between gap-4 px-5 py-4 hover:bg-canvas/60 [&::-webkit-details-marker]:hidden">
                <h{{ $headingLevel }} class="text-base font-semibold text-ink">{{ $faq['question'] }}</h{{ $headingLevel }}>
                <x-ui.icon name="chevron-down" class="mt-0.5 size-5 text-muted transition-transform group-open:rotate-180" />
            </summary>
            <div class="px-5 pb-5 text-[0.9375rem] leading-7 text-body faq-answer">
                {!! $faq['answer'] !!}
            </div>
        </details>
    @endforeach
</div>
