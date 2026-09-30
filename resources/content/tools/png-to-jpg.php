<?php

/*
| Page text (heading, introduction, summary, card text, features, sections and FAQs) lives in
| resources/content/tool-content/png-to-jpg.php and can be edited in the admin.
*/

return [
    'path' => 'tools/png-to-jpg',
    'widget' => 'compressor',
    'category' => 'convert',
    'name' => 'PNG to JPG Converter',
    'icon' => 'refresh',
    'title' => 'PNG to JPG Converter: Convert PNG to JPG Free',
    'description' => 'Convert PNG to JPG online for free in your browser. Shrink photos and screenshots saved as PNG, set quality or a target size, and download a .jpg file.',
    'updated_at' => '2026-09-16',
    'figure' => 'png-to-jpg-transparency',
    'widget_options' => ['input' => 'image/png', 'output' => 'image/jpeg'],
    'related_tools' => ['png-to-webp', 'image-compressor', 'webp-to-jpg'],
];
