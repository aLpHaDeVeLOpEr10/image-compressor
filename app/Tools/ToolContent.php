<?php

namespace App\Tools;

use App\Support\SiteIdentity;
use Illuminate\Support\Facades\Route;

/**
 * Helpers for key/value page content managed in the admin: placeholder replacement and numbered lists.
 */
final class ToolContent
{
    /**
     * Replace {placeholders} with live values, so links and limits stay correct when routes or config change.
     * Supported: {brand}, {year}, {operator}, {max_upload_mb}, {max_megapixels}, {server_max_megapixels},
     * {max_custom_target_kb}, {default_quality} and {url:route.name}. Unknown placeholders are left untouched.
     */
    public static function resolve(string $value): string
    {
        if (! str_contains($value, '{')) {
            return $value;
        }

        return (string) preg_replace_callback('/\{(url:)?([a-z0-9_.-]+)\}/i', function (array $match): string {
            if ($match[1] !== '') {
                return Route::has($match[2]) ? route($match[2]) : $match[0];
            }

            return match ($match[2]) {
                'brand' => (string) config('site.brand'),
                'year' => (string) now()->year,
                'operator' => SiteIdentity::operatorName(),
                'max_upload_mb' => (string) config('compressor.max_upload_mb'),
                'max_megapixels' => number_format(config('compressor.max_pixels') / 1_000_000),
                'server_max_megapixels' => number_format(config('compressor.server_max_pixels') / 1_000_000),
                'max_custom_target_kb' => (string) config('compressor.max_custom_target_kb'),
                'default_quality' => (string) config('compressor.default_quality'),
                default => $match[0],
            };
        }, $value);
    }

    /**
     * Collect numbered keys into an ordered list, e.g. feature_1, feature_2 or faq_1_question, faq_1_answer.
     * Items whose first part is empty are skipped, so clearing a value hides that item.
     *
     * @param  array<string, string>  $content
     * @param  array<int, string>  $parts
     * @return ($parts is non-empty-array ? array<int, array<string, string>> : array<int, string>)
     */
    public static function list(array $content, string $prefix, array $parts = []): array
    {
        $pattern = $parts === []
            ? '/^'.preg_quote($prefix, '/').'_(\d+)$/'
            : '/^'.preg_quote($prefix, '/').'_(\d+)_('.implode('|', array_map(fn (string $part) => preg_quote($part, '/'), $parts)).')$/';

        $items = [];

        foreach ($content as $key => $value) {
            if (preg_match($pattern, $key, $match)) {
                $parts === [] ? $items[(int) $match[1]] = $value : $items[(int) $match[1]][$match[2]] = $value;
            }
        }

        ksort($items);

        if ($parts === []) {
            return array_values(array_filter($items, fn (string $value) => trim($value) !== ''));
        }

        return array_values(array_filter(
            array_map(fn (array $item) => array_merge(array_fill_keys($parts, ''), $item), $items),
            fn (array $item) => trim($item[$parts[0]]) !== '',
        ));
    }

    /**
     * Replace a figure's alt text and caption with content values, when they are set.
     *
     * @param  array<string, mixed>|null  $figure
     * @param  array<string, string>  $content
     * @return array<string, mixed>|null
     */
    public static function figure(?array $figure, array $content, string $prefix): ?array
    {
        if ($figure === null) {
            return null;
        }

        foreach (['alt', 'caption'] as $part) {
            if (trim($content["{$prefix}_{$part}"] ?? '') !== '') {
                $figure[$part] = $content["{$prefix}_{$part}"];
            }
        }

        return $figure;
    }
}
