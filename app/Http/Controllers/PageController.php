<?php

namespace App\Http\Controllers;

use App\Support\Seo\Seo;
use App\Support\Seo\StructuredData;
use App\Tools\ToolRegistry;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public const LEGAL_PAGES = [
        'privacy-policy' => [
            'title' => 'Privacy Policy',
            'description' => 'How CompressPix handles your images, contact messages, cookies and log data, including when images are processed in your browser or on our server.',
        ],
        'terms-and-conditions' => [
            'title' => 'Terms & Conditions',
            'description' => 'The terms for using CompressPix\'s free online image compression tools, including acceptable use, your content and limitations of liability.',
        ],
        'cookie-policy' => [
            'title' => 'Cookie Policy',
            'description' => 'Which cookies the CompressPix image compressor website uses, why each one is needed, how long they last and how you can manage them.',
        ],
        'disclaimer' => [
            'title' => 'Disclaimer',
            'description' => 'Important limits of the CompressPix image compression tools: approximate target sizes, quality loss, results that vary by image and general information.',
        ],
    ];

    public function about(ToolRegistry $tools): View
    {
        return view('pages.about', [
            'seo' => $this->seo(
                'About CompressPix: Who Builds It and How It Works',
                'Who operates CompressPix, how the free image compressor works in your browser and on our server, how we test results and how to contact us.',
                'pages.about',
            )->asPage('AboutPage', StructuredData::organizationId()),
            'relatedTools' => $tools->all(),
        ]);
    }

    public function editorial(): View
    {
        return view('pages.editorial-policy', [
            'seo' => $this->seo(
                'Editorial Policy: How We Write and Check Our Pages',
                'How CompressPix researches, tests, reviews and updates its tool pages and help content, and how to report a mistake.',
                'pages.editorial',
            ),
        ]);
    }

    public function faq(): View
    {
        $groups = require resource_path('content/faq.php');

        $seo = $this->seo(
            'Image Compression FAQ',
            'Answers to common questions about compressing JPG, PNG and WebP images, quality loss, target file sizes, privacy, limits and downloads.',
            'pages.faq',
        )->asPage('FAQPage');

        $seo->pageProperties = ['mainEntity' => StructuredData::questions(collect($groups)->pluck('items')->flatten(1)->all())];

        return view('pages.faq', compact('seo', 'groups'));
    }

    public function legal(string $page): View
    {
        $meta = self::LEGAL_PAGES[$page];

        return view("pages.legal.{$page}", [
            'seo' => $this->seo($meta['title'], $meta['description'], "pages.{$page}"),
            'title' => $meta['title'],
        ]);
    }

    private function seo(string $title, string $description, string $route): Seo
    {
        $seo = Seo::make($title, $description)
            ->withBreadcrumbs($this->breadcrumbs([$this->breadcrumbLabel($title) => route($route)]))
            ->updatedAt(config('site.pages_updated_at'));
        $seo->canonical = route($route);

        return $seo;
    }

    private function breadcrumbLabel(string $title): string
    {
        return trim(explode(':', $title)[0]);
    }
}
