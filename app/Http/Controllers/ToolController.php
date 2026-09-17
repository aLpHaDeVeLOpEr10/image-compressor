<?php

namespace App\Http\Controllers;

use App\Support\Figures;
use App\Support\Seo\Seo;
use App\Support\Seo\StructuredData;
use App\Tools\ToolContent;
use App\Tools\ToolPage;
use App\Tools\ToolRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class ToolController extends Controller
{
    public function show(ToolRegistry $tools, string $tool): View
    {
        abort_unless($tools->all()->has($tool), 404);

        return $this->render($tools, $tools->get($tool));
    }

    /**
     * A tool added in the admin, at /tools/{slug}.
     */
    public function showCustom(ToolRegistry $tools, string $slug): View
    {
        $page = $tools->findCustom($slug);

        abort_unless($page?->isPublished(), 404);

        return $this->render($tools, $page);
    }

    /**
     * A language version of a tool, at /{locale}/{slug} or /{locale} for the homepage.
     */
    public function showLocalized(ToolRegistry $tools, string $locale, ?string $slug = null): View
    {
        $page = $tools->findChild($locale, $slug);

        abort_unless($page?->isPublished(), 404);

        $defaultLocale = ToolPage::defaultLocale();
        $appLocale = app()->getLocale();

        app()->setLocale($locale);
        config(['site.default_locale' => $defaultLocale, 'site.locale' => $locale]);

        // Restore the site language afterwards so long-running workers (and tests) do not leak it into the next request.
        app()->terminating(function () use ($defaultLocale, $appLocale) {
            config(['site.default_locale' => null, 'site.locale' => $defaultLocale]);
            app()->setLocale($appLocale);
        });

        return $this->render($tools, $page);
    }

    private function render(ToolRegistry $tools, ToolPage $page): View
    {
        $locale = $page->locale;
        $figure = ToolContent::figure(Figures::get($page->figure), $page->content, 'figure');
        $localize = fn (Collection $pages) => $pages
            ->map(fn (ToolPage $other) => $tools->localized($other->key, $locale))
            ->filter()
            ->keyBy(fn (ToolPage $other) => $other->familyKey());

        $seo = Seo::make($page->title, $page->description)
            ->withBreadcrumbs($this->toolBreadcrumbs($tools, $page))
            ->withImage($page->image ?? $figure)
            ->asPage('WebPage', StructuredData::applicationId($page))
            ->updatedAt($page->updatedAt->toDateString())
            ->withSchema(StructuredData::webApplication($page));

        $seo->canonical = $page->url();
        $seo->alternates = $this->alternates($tools, $page);

        $languageVersions = collect($seo->alternates)
            ->except('x-default')
            ->map(fn (string $url, string $code) => [
                'code' => $code,
                'name' => $this->languageName($code),
                'url' => $url,
                'current' => $url === $page->url(),
            ])
            ->values();

        return view('tools.show', [
            'seo' => $seo,
            'tool' => $page,
            'content' => $page->content,
            'figure' => $figure,
            'groups' => $page->isHomepage() ? $tools->grouped()->map(fn ($group) => $localize($group->reject(fn ($other) => $other->isPrimary())))->filter(fn ($group) => $group->isNotEmpty()) : collect(),
            'categoryLabel' => config("tools.categories.{$page->category}"),
            'categoryTools' => $localize($tools->all()->filter(fn ($other) => $other->category === $page->category)),
            'relatedTools' => $localize($tools->only($page->relatedTools)),
            'languageVersions' => $languageVersions,
        ]);
    }

    /**
     * A language's name in that language (e.g. "Español"), falling back to the configured English label.
     */
    private function languageName(string $code): string
    {
        $name = extension_loaded('intl') ? \Locale::getDisplayLanguage($code, $code) : '';

        if ($name === '' || $name === $code) {
            $name = config("tools.languages.{$code}") ?? ($code === 'en' ? 'English' : strtoupper($code));
        }

        return mb_convert_case(mb_substr($name, 0, 1), MB_CASE_UPPER).mb_substr($name, 1);
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function toolBreadcrumbs(ToolRegistry $tools, ToolPage $page): array
    {
        if ($page->isHomepage()) {
            return [];
        }

        $home = $tools->localized(config('tools.primary'), $page->locale);

        return [
            ['label' => 'Home', 'url' => $home?->url() ?? route('home')],
            ['label' => $page->name, 'url' => $page->url()],
        ];
    }

    /**
     * hreflang alternates for every published language version of the page, with the parent as x-default.
     *
     * @return array<string, string>
     */
    private function alternates(ToolRegistry $tools, ToolPage $page): array
    {
        $parent = $tools->all()->get($page->familyKey());
        $translations = $tools->translations($page->familyKey());

        if ($translations->isEmpty()) {
            return [];
        }

        return collect($parent ? [$parent->language() => $parent->url()] : [])
            ->merge($translations->mapWithKeys(fn (ToolPage $child) => [$child->locale => $child->url()]))
            ->put('x-default', $parent?->url() ?? $page->url())
            ->all();
    }
}
