<?php

namespace App\Http\Controllers;

use App\Support\Figures;
use App\Support\SiteIdentity;
use App\Tools\ToolPage;
use App\Tools\ToolRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SeoController extends Controller
{
    /**
     * Indexable static pages included in the XML sitemap.
     */
    private const PAGES = [
        'pages.about',
        'pages.editorial',
        'pages.faq',
        'pages.contact',
        'pages.privacy-policy',
        'pages.terms-and-conditions',
        'pages.cookie-policy',
        'pages.disclaimer',
    ];

    public function sitemap(ToolRegistry $tools): Response
    {
        return response()
            ->view('seo.sitemap', ['urls' => $this->sitemapUrls($tools)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @return Collection<int, array{loc: string, lastmod: string, images: array<int, array{loc: string, title: string}>}>
     */
    private function sitemapUrls(ToolRegistry $tools): Collection
    {
        $pagesUpdated = Carbon::parse(config('site.pages_updated_at'))->toDateString();

        return $tools->all()->flatMap(fn (ToolPage $tool) => [$tool, ...$tools->translations($tool->key)->values()])->values()->map(function (ToolPage $tool) {
            $figure = Figures::get($tool->figure);

            return [
                'loc' => $tool->url(),
                'lastmod' => $tool->updatedAt->toDateString(),
                'images' => $figure ? [['loc' => asset($figure['src']), 'title' => $figure['alt']]] : [],
            ];
        })
            ->concat(collect(self::PAGES)->map(fn (string $route) => [
                'loc' => route($route),
                'lastmod' => $pagesUpdated,
                'images' => [],
            ]));
    }

    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => config('site.brand').' – Image Compressor',
            'short_name' => config('site.brand'),
            'description' => config('site.description'),
            'start_url' => route('home'),
            'icons' => [
                ['src' => asset(config('site.assets.apple_touch_icon')), 'sizes' => '180x180', 'type' => 'image/png'],
                ['src' => asset(config('site.assets.logo_png')), 'sizes' => '512x512', 'type' => 'image/png'],
                ['src' => asset(config('site.assets.favicon_svg')), 'sizes' => 'any', 'type' => 'image/svg+xml'],
            ],
            'theme_color' => config('site.assets.theme_color'),
            'background_color' => '#ffffff',
            'display' => 'browser',
        ], 200, ['Content-Type' => 'application/manifest+json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function robots(): Response
    {
        $lines = config('site.indexable')
            ? [
                'User-agent: *',
                'Allow: /',
                'Disallow: /process/',
                'Disallow: /up',
                'Disallow: /storage/',
                '',
                'Sitemap: '.route('seo.sitemap'),
            ]
            : [
                '# This environment is not public. Pages send "X-Robots-Tag: noindex, nofollow".',
                'User-agent: *',
                'Allow: /',
            ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * A plain-text overview of the site for AI assistants and answer engines (https://llmstxt.org).
     */
    public function llms(ToolRegistry $tools): Response
    {
        $brand = config('site.brand');
        $lines = [
            "# {$brand}",
            '',
            '> '.config('site.description'),
            '',
            (SiteIdentity::value('company_name') && SiteIdentity::value('company_name') !== $brand ? "{$brand} is operated by ".SiteIdentity::value('company_name').'. ' : '').'JPG compression runs in the visitor\'s browser; WebP and PNG output also run in the browser where supported, with an in-memory server fallback that does not store images. Supported inputs: JPG/JPEG, PNG and WebP up to '.config('compressor.max_upload_mb').' MB, one image at a time. Target sizes are approximate, with 1 KB = 1,024 bytes.',
            '',
        ];

        foreach ($tools->grouped() as $label => $group) {
            $lines[] = "## {$label}";
            $lines[] = '';

            foreach ($group as $tool) {
                $lines[] = "- [{$tool->name}]({$tool->url()}): {$tool->cardDescription}";
            }

            $lines[] = '';
        }

        $lines = [...$lines, '## About', '',
            '- [About '.$brand.']('.route('pages.about').'): who operates the site and how the compressor works',
            '- [Editorial policy]('.route('pages.editorial').'): how our tool pages and help content are written and checked',
            '- [FAQ]('.route('pages.faq').')',
            '- [Privacy policy]('.route('pages.privacy-policy').')',
            '- [Contact]('.route('pages.contact').')',
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
