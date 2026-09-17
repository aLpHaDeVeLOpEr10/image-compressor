@php($ga4 = config('site.analytics.ga4_id'))

@if ($ga4 && app()->isProduction())
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga4) }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @json($ga4), { anonymize_ip: true });
    </script>
@endif
