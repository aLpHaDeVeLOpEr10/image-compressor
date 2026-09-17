<?php

/*
| Page text (heading, introduction, summary, card text, features, sections and FAQs) lives in
| resources/content/tool-content/webp-to-jpg.php and can be edited in the admin.
*/

return [
    'path' => 'tools/webp-to-jpg',
    'widget' => 'compressor',
    'category' => 'convert',
    'name' => 'WebP to JPG Converter',
    'icon' => 'refresh',
    'title' => 'WebP to JPG Converter: Convert WebP to JPG Free',
    'description' => 'Convert WebP to JPG online for free. Open and upload WebP images in apps, forms and print services that only accept JPG. Runs in your browser, no sign-up.',
    'updated_at' => '2026-09-16',
    'figure' => 'webp-to-jpg-sizes',
    'widget_options' => ['output' => 'image/jpeg'],
    'related_tools' => ['jpg-to-webp', 'png-to-jpg', 'image-compressor'],
];
