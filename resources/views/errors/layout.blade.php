@php
    $seo = new \App\Support\Seo\Seo(title: $title, description: $message, robots: 'noindex, follow');
@endphp

<x-layouts.app :seo="$seo">
    <x-ui.container class="flex flex-col items-center py-20 text-center sm:py-28">
        <p class="text-sm font-semibold text-brand-700">Error {{ $code }}</p>
        <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ $title }}</h1>
        <p class="mt-4 max-w-lg text-base leading-7 text-muted">{{ $message }}</p>
        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <x-ui.button :href="route('home')">Go to homepage</x-ui.button>
            <x-ui.button :href="route('home')" variant="secondary">Open Image Compressor</x-ui.button>
        </div>
    </x-ui.container>
</x-layouts.app>
