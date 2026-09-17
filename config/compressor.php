<?php

return [

    'max_upload_mb' => (int) env('COMPRESSOR_MAX_UPLOAD_MB', 20),

    'max_pixels' => (int) env('COMPRESSOR_MAX_PIXELS', 50_000_000),

    'server_max_pixels' => (int) env('COMPRESSOR_SERVER_MAX_PIXELS', 25_000_000),

    'default_quality' => (int) env('COMPRESSOR_DEFAULT_QUALITY', 80),

    'min_quality' => 10,

    'max_quality' => 100,

    'rate_limit_per_minute' => (int) env('COMPRESSOR_RATE_LIMIT_PER_MINUTE', 30),

    'formats' => [
        'image/jpeg' => ['label' => 'JPEG', 'extensions' => ['jpg', 'jpeg']],
        'image/png' => ['label' => 'PNG', 'extensions' => ['png']],
        'image/webp' => ['label' => 'WebP', 'extensions' => ['webp']],
    ],

    'output_formats' => [
        'original' => 'Same as original',
        'image/jpeg' => 'JPG',
        'image/webp' => 'WebP',
    ],

    'target_presets' => [
        50 => '50 KB',
        100 => '100 KB',
        200 => '200 KB',
        500 => '500 KB',
        1024 => '1 MB',
    ],

    'max_custom_target_kb' => 20480,

];
