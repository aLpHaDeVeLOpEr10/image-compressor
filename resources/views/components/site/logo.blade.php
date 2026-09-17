<a href="{{ app(\App\Tools\ToolRegistry::class)->localized(config('tools.primary'), app()->getLocale())?->url() ?? route('home') }}" {{ $attributes->class('inline-flex items-center gap-2.5 rounded-md') }} aria-label="{{ config('site.brand') }} home">
    <img src="{{ asset(config('site.assets.logo_mark')) }}" alt="" width="32" height="32" class="size-8">
    <span class="text-lg font-bold tracking-tight text-ink">{{ config('site.brand') }}</span>
</a>
