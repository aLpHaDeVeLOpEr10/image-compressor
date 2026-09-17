@props(['seo' => null])

@php($seo ??= \App\Support\Seo\Seo::fallback())

<!DOCTYPE html>
<html lang="{{ config('site.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo.meta :seo="$seo" />

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    <x-seo.analytics />
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only z-50 rounded-md bg-brand-600 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">{{ app(\App\Tools\ToolRegistry::class)->siteContent()['site_skip_link'] ?? 'Skip to content' }}</a>

    <x-site.header />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-site.footer />

    @stack('scripts')
</body>
</html>
