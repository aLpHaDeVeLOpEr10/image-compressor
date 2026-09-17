@props(['seo'])

@php
    $robots = config('site.indexable') ? $seo->robots : 'noindex, nofollow';
    $assets = config('site.assets');
    [$imageWidth, $imageHeight] = $seo->imageSize();
    $jsonLd = $seo->jsonLd();
@endphp

<title>{{ $seo->fullTitle() }}</title>
<meta name="description" content="{{ $seo->description }}">
<meta name="robots" content="{{ $robots }}">
@if ($seo->canonical)
    <link rel="canonical" href="{{ $seo->canonical }}">
@endif
@foreach ($seo->alternates as $hreflang => $alternateUrl)
    <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $alternateUrl }}">
@endforeach

<meta property="og:site_name" content="{{ config('site.brand') }}">
<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:title" content="{{ $seo->title }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->canonical ?? url()->current() }}">
<meta property="og:image" content="{{ $seo->imageUrl() }}">
<meta property="og:image:width" content="{{ $imageWidth }}">
<meta property="og:image:height" content="{{ $imageHeight }}">
<meta property="og:image:alt" content="{{ $seo->imageAlt() }}">
<meta property="og:locale" content="{{ config('site.og_locale') }}">
@if ($seo->publishedTime)
    <meta property="article:published_time" content="{{ $seo->publishedTime }}">
@endif
@if ($seo->type === 'article' && $seo->modifiedTime)
    <meta property="article:modified_time" content="{{ $seo->modifiedTime }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->title }}">
<meta name="twitter:description" content="{{ $seo->description }}">
<meta name="twitter:image" content="{{ $seo->imageUrl() }}">
<meta name="twitter:image:alt" content="{{ $seo->imageAlt() }}">
@if (config('site.twitter_handle'))
    <meta name="twitter:site" content="{{ config('site.twitter_handle') }}">
@endif

<meta name="theme-color" content="{{ $assets['theme_color'] }}">
<link rel="icon" href="{{ asset($assets['favicon_ico']) }}" sizes="32x32">
<link rel="icon" href="{{ asset($assets['favicon_svg']) }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset($assets['apple_touch_icon']) }}">
<link rel="manifest" href="{{ route('seo.manifest') }}">

@if ($jsonLd)
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endif
