<?php

/*
| Page text (heading, introduction, summary, card text, features, sections and FAQs) lives in
| resources/content/tool-content/jpg-to-webp.php and can be edited in the admin.
*/

return [
    'path' => 'tools/jpg-to-webp',
    'widget' => 'compressor',
    'category' => 'convert',
    'name' => 'JPG to WebP Converter',
    'icon' => 'refresh',
    'title' => 'JPG to WebP Converter: Convert JPG to WebP Free',
    'description' => 'Convert JPG to WebP online for free. Make lighter images for websites, blogs and online stores, compare file sizes and download the .webp file instantly.',
    'updated_at' => '2026-09-16',
    'figure' => 'jpg-to-webp-sizes',
    'widget_options' => ['input' => 'image/jpeg', 'output' => 'image/webp'],
    'related_tools' => ['webp-to-jpg', 'image-compressor', 'png-to-webp'],
];
