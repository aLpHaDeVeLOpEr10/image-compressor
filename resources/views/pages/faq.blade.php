<x-layouts.app :seo="$seo">
    <x-ui.page-header
        title="Image Compression FAQ"
        description="Answers about how our image compressor works, formats, quality, target sizes, privacy and downloads."
        :breadcrumbs="$seo->breadcrumbs"
    />

    <x-ui.container class="py-12 sm:py-16">
        <div class="grid gap-10 lg:grid-cols-[14rem_minmax(0,1fr)]">
            <nav aria-label="FAQ topics" class="lg:sticky lg:top-24 lg:self-start">
                <p class="text-sm font-semibold text-ink">Topics</p>
                <ul class="mt-3 flex flex-wrap gap-2 lg:flex-col lg:gap-1">
                    @foreach ($groups as $group)
                        <li>
                            <a href="#{{ $group['id'] }}" class="block rounded-md border border-line px-3 py-1.5 text-sm text-body hover:border-brand-300 hover:text-brand-700 lg:border-0 lg:px-2">{{ $group['title'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="max-w-3xl space-y-12">
                @foreach ($groups as $group)
                    <section id="{{ $group['id'] }}" class="scroll-mt-24" aria-labelledby="{{ $group['id'] }}-heading">
                        <h2 id="{{ $group['id'] }}-heading" class="text-xl font-bold tracking-tight sm:text-2xl">{{ $group['title'] }}</h2>
                        <x-ui.faq :faqs="$group['items']" class="mt-5" />
                    </section>
                @endforeach

                <x-ui.alert type="info" title="Still have a question?">
                    <a href="{{ route('pages.contact') }}" class="font-semibold underline">Contact us</a> and we will do our best to help.
                </x-ui.alert>
            </div>
        </div>
    </x-ui.container>

    <x-content.cta />
</x-layouts.app>
