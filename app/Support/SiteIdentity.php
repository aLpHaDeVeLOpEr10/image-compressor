<?php

namespace App\Support;

/**
 * Reads the operator identity from config, treating empty or placeholder values as "not configured"
 * so unfinished details are never published.
 */
final class SiteIdentity
{
    /**
     * Returns a config value under "site." or null when it is empty or still a placeholder.
     */
    public static function value(string $key): ?string
    {
        $value = trim((string) config("site.{$key}"));

        return $value === '' || self::isPlaceholder($value) ? null : $value;
    }

    public static function isPlaceholder(string $value): bool
    {
        return (bool) preg_match('/\[[^\]]*\]|example\.(com|org|net)|^changeme$/i', $value);
    }

    /**
     * The legal operator name, falling back to the brand when no company name is configured.
     */
    public static function operatorName(): string
    {
        return self::value('company_name') ?? (string) config('site.brand');
    }

    /**
     * @return array<string, string> config key => human label, for every identity value still missing
     */
    public static function missing(): array
    {
        $labels = [
            'company_name' => 'SITE_COMPANY_NAME (legal operator name)',
            'country' => 'SITE_COUNTRY (country the operator is based in)',
            'legal.hosting_provider' => 'SITE_HOSTING_PROVIDER (privacy policy)',
            'legal.jurisdiction' => 'SITE_JURISDICTION (terms: governing law)',
            'legal.contact_retention' => 'SITE_CONTACT_RETENTION (privacy policy, e.g. "12 months")',
            'legal.log_retention' => 'SITE_LOG_RETENTION (privacy policy, e.g. "30 days")',
        ];

        return array_filter($labels, fn (string $label, string $key) => self::value($key) === null, ARRAY_FILTER_USE_BOTH);
    }
}
