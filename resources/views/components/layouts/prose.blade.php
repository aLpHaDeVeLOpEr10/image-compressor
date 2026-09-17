@props(['seo', 'title', 'description' => null, 'updated' => null])

<x-layouts.app :seo="$seo">
    <x-ui.page-header :title="$title" :description="$description" :breadcrumbs="$seo->breadcrumbs">
        @if ($updated)
            <x-slot:meta>Last updated: <time datetime="{{ \Illuminate\Support\Carbon::parse($updated)->toDateString() }}">{{ \Illuminate\Support\Carbon::parse($updated)->format('F j, Y') }}</time></x-slot:meta>
        @endif
    </x-ui.page-header>

    <x-ui.container class="py-12 sm:py-16">
        <div class="prose-content max-w-3xl">
            {{ $slot }}
        </div>
    </x-ui.container>
</x-layouts.app>
