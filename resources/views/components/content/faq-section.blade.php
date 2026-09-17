@props(['faqs', 'title' => 'Frequently asked questions', 'description' => null, 'more' => null])

@if (! empty($faqs))
    <section {{ $attributes->class('section') }} aria-labelledby="faq-heading" id="faq">
        <x-ui.container class="max-w-3xl">
            <x-ui.section-heading id="faq-heading" :title="$title" :description="$description" />
            <x-ui.faq :faqs="$faqs" class="mt-8" />
            @if ($more !== '')
                <p class="mt-6 text-center text-sm text-muted">
                    @if ($more === null)
                        More answers in our <a href="{{ route('pages.faq') }}" class="link">image compression FAQ</a>.
                    @else
                        {!! $more !!}
                    @endif
                </p>
            @endif
        </x-ui.container>
    </section>
@endif
