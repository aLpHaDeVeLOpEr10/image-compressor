<?php

return [

    'content_path' => resource_path('content/tools'),

    /*
    | Default key/value page content editable in the admin: _shared.php for every tool page, _site.php for the
    | header and footer (edited on the primary tool), and <tool-key>.php for a tool's own content.
    */
    'content_defaults_path' => resource_path('content/tool-content'),

    /*
    | Where the content Blade views of tools live. Tools added in the admin get a starter view created here.
    */
    'content_views_path' => resource_path('views/tools/content'),

    /*
    | URL prefix for every tool page except the primary one, e.g. /tools/png-to-jpg.
    */
    'prefix' => 'tools',

    /*
    | The main tool, served on the homepage. It covers every compression mode:
    | JPG, PNG and WebP compression and target sizes.
    */
    'primary' => 'image-compressor',

    'pages' => [
        'image-compressor',
        'png-to-jpg',
        'jpg-to-webp',
        'png-to-webp',
        'webp-to-jpg',
    ],

    /*
    | Tool groups, in display order, used by the footer and llms.txt.
    */
    'categories' => [
        'general' => 'Image compressor',
        'convert' => 'Converters',
    ],

    /*
    | Languages that child tools (translated versions of a tool) can use, as locale => label. Child tools are
    | served at /{locale}/{slug}, or /{locale} for a child of the primary tool. The site's own language is
    | config('site.locale').
    */
    'languages' => [
        'ar' => 'Arabic',
        'bn' => 'Bengali',
        'de' => 'German',
        'es' => 'Spanish',
        'fr' => 'French',
        'hi' => 'Hindi',
        'id' => 'Indonesian',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'nl' => 'Dutch',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'ru' => 'Russian',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'vi' => 'Vietnamese',
        'zh' => 'Chinese',
    ],

    'navigation' => [
        'png-to-jpg' => 'PNG to JPG',
        'jpg-to-webp' => 'JPG to WebP',
        'png-to-webp' => 'PNG to WebP',
        'webp-to-jpg' => 'WebP to JPG',
    ],

];
