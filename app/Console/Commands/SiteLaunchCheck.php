<?php

namespace App\Console\Commands;

use App\Support\SiteIdentity;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('site:launch-check')]
#[Description('Check that SEO-critical settings and operator details are ready for a public launch')]
class SiteLaunchCheck extends Command
{
    public function handle(): int
    {
        $problems = [];
        $url = (string) config('app.url');
        $host = (string) parse_url($url, PHP_URL_HOST);

        if (! str_starts_with($url, 'https://')) {
            $problems[] = "APP_URL must use https:// (currently \"{$url}\"). Canonicals, sitemap and structured data are built from it.";
        }

        if ($host === '' || in_array($host, ['localhost', '127.0.0.1'], true) || str_ends_with($host, '.test') || str_ends_with($host, '.local') || SiteIdentity::isPlaceholder($url)) {
            $problems[] = "APP_URL must be the final public host (currently \"{$url}\").";
        }

        if (! config('site.indexable')) {
            $problems[] = 'SITE_INDEXABLE is false, so every page sends noindex.';
        }

        if (config('app.debug')) {
            $problems[] = 'APP_DEBUG must be false in production.';
        }

        foreach (SiteIdentity::missing() as $label) {
            $problems[] = "Missing operator detail: {$label}.";
        }

        if ($problems === []) {
            $this->components->info('Ready for launch: URL, indexing and operator details are configured.');

            return self::SUCCESS;
        }

        $this->components->error('Not ready for launch:');
        $this->components->bulletList($problems);

        return self::FAILURE;
    }
}
