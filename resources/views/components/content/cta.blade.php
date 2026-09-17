@props(['title' => 'Ready to compress your images?', 'text' => 'Upload a JPG, PNG or WebP image and download a smaller version in seconds. Free, with no registration.', 'href' => null, 'label' => 'Compress Image'])

<section {{ $attributes->class('section') }}>
    <x-ui.container>
        <div class="rounded-2xl border border-brand-100 bg-brand-50 px-6 py-10 text-center sm:px-12 sm:py-14">
            <h2 class="section-title">{{ $title }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-base leading-7 text-body">{{ $text }}</p>
            <x-ui.button :href="$href ?? route('home')" size="lg" icon="upload" class="mt-6">{{ $label }}</x-ui.button>
        </div>
    </x-ui.container>
</section>
