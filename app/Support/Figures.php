<?php

namespace App\Support;

/**
 * Registry of the original example figures produced by `php artisan images:generate-examples`.
 */
final class Figures
{
    /** @var array<string, array{src: string, width: int, height: int, alt: string, caption: string, sources: array<int, array{src: string, width: int}>}>|null */
    private static ?array $figures = null;

    /**
     * @return array{src: string, width: int, height: int, alt: string, caption: string, sources: array<int, array{src: string, width: int}>}|null
     */
    public static function get(?string $key): ?array
    {
        if ($key === null) {
            return null;
        }

        return self::all()[$key] ?? null;
    }

    /**
     * @return array<string, array{src: string, width: int, height: int, alt: string, caption: string, sources: array<int, array{src: string, width: int}>}>
     */
    public static function all(): array
    {
        if (self::$figures === null) {
            $path = self::path();
            self::$figures = is_file($path) ? (array) json_decode((string) file_get_contents($path), true) : [];
        }

        return self::$figures;
    }

    /**
     * @return array{src: string, width: int, height: int, alt: string, caption: string, sources: array<int, array{src: string, width: int}>}|null
     */
    public static function bySrc(?string $src): ?array
    {
        return collect(self::all())->firstWhere('src', $src);
    }

    public static function path(): string
    {
        return resource_path('content/figures.json');
    }

    public static function flush(): void
    {
        self::$figures = null;
    }
}
