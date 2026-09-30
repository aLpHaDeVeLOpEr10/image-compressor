<?php

/*
| Page text (heading, introduction, summary, card text, features, sections and FAQs) lives in
| resources/content/tool-content/png-to-webp.php and can be edited in the admin.
*/

return [
    'path' => 'tools/png-to-webp',
    'widget' => 'compressor',
    'category' => 'convert',
    'name' => 'PNG to WebP Converter',
    'icon' => 'refresh',
    'title' => 'PNG to WebP Converter: Keep Transparency, Free',
    'description' => 'Convert PNG to WebP online for free and keep transparency. Get lighter product cut-outs and illustrations for the web, then download the .webp file.',
    'updated_at' => '2026-09-16',
    'figure' => 'png-to-webp-transparency',
    'widget_options' => ['input' => 'image/png', 'output' => 'image/webp'],
    'related_tools' => ['png-to-jpg', 'image-compressor', 'jpg-to-webp'],
];
