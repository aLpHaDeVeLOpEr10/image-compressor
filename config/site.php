<?php

return [

    'brand' => env('SITE_BRAND', 'PicsCompressor'),

    'tagline' => env('SITE_TAGLINE', 'Free online image compressor'),

    'description' => 'PicsCompressor is a free, browser-based image compressor for JPG, PNG and WebP. Reduce file size, hit a target size such as 100KB, convert formats and compare before and after.',

    'url' => rtrim(env('APP_URL', 'http://localhost'), '/'),

    /*
    | Secret path for the admin sign-in page, so the login form is not sitting
    | at a guessable URL. Everything else stays under /admin, which guests get
    | a 404 for. Change it here or with ADMIN_LOGIN_PATH.
    */

    'admin_login_path' => trim(env('ADMIN_LOGIN_PATH', 'jhasseaidsha12'), '/'),

    /*
    |--------------------------------------------------------------------------
    | Operator identity
    |--------------------------------------------------------------------------
    |
    | These values are shown on the About, Contact and legal pages and in the
    | Organization structured data. Values that are empty or still look like
    | placeholders (e.g. "[Company Name]" or "example.com") are treated as not
    | configured. Run `php artisan site:launch-check` before going live.
    |
    */

    'company_name' => env('SITE_COMPANY_NAME', 'PicsCompressor'),

    'country' => env('SITE_COUNTRY'),

    'founding_year' => env('SITE_FOUNDING_YEAR'),

    'legal' => [
        'hosting_provider' => env('SITE_HOSTING_PROVIDER'),
        'jurisdiction' => env('SITE_JURISDICTION'),
        'contact_retention' => env('SITE_CONTACT_RETENTION'),
        'log_retention' => env('SITE_LOG_RETENTION'),
    ],

    'locale' => 'en',

    'og_locale' => 'en_US',

    'indexable' => (bool) env('SITE_INDEXABLE', env('APP_ENV') === 'production'),

    'twitter_handle' => env('SITE_TWITTER_HANDLE'),

    /*
    | Verification token for Google Search Console, rendered as a meta tag in
    | the head of every page. Keep it in place; removing it un-verifies the site.
    */

    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION'),

    'social' => array_filter([
        'X' => env('SITE_SOCIAL_TWITTER'),
        'Facebook' => env('SITE_SOCIAL_FACEBOOK'),
        'LinkedIn' => env('SITE_SOCIAL_LINKEDIN'),
        'GitHub' => env('SITE_SOCIAL_GITHUB'),
    ]),

    'assets' => [
        'logo' => 'images/brand/logo.svg',
        'logo_mark' => 'images/brand/logo-mark.svg',
        'logo_png' => 'images/brand/logo-512.png',
        'favicon_svg' => 'favicon.svg',
        'favicon_ico' => 'favicon.ico',
        'apple_touch_icon' => 'apple-touch-icon.png',
        'og_image' => 'images/brand/og-image.png',
        'theme_color' => '#2459e0',
    ],

    /*
    | Date the static pages (home, about, FAQ, legal) were last substantively
    | updated. Used for sitemap lastmod and visible "last updated" dates.
    */
    'pages_updated_at' => '2026-09-16',

    'contact' => [
        'notify_email' => env('CONTACT_NOTIFY_EMAIL'),
        'send_mail' => (bool) env('CONTACT_SEND_MAIL', false),
    ],

    'analytics' => [
        'ga4_id' => env('ANALYTICS_GA4_ID'),
    ],

];
