<?php

namespace App\Support\Seo;

use App\Support\Figures;
use App\Support\SiteIdentity;
use App\Tools\ToolPage;
use Illuminate\Support\Collection;

/**
 * Builds schema.org nodes that are joined into one @graph per page through stable @id references.
 */
final class StructuredData
{
    public static function homeUrl(): string
    {
        return rtrim(route('home'), '/').'/';
    }

    public static function organizationId(): string
    {
        return self::homeUrl().'#organization';
    }

    public static function websiteId(): string
    {
        return self::homeUrl().'#website';
    }

    public static function webPageId(string $url): string
    {
        return "{$url}#webpage";
    }

    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $country = SiteIdentity::value('country');

        return array_filter([
            '@type' => 'Organization',
            '@id' => self::organizationId(),
            'name' => config('site.brand'),
            'legalName' => SiteIdentity::value('company_name'),
            'url' => self::homeUrl(),
            'description' => config('site.description'),
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => self::homeUrl().'#logo',
                'url' => asset(config('site.assets.logo_png')),
                'contentUrl' => asset(config('site.assets.logo_png')),
                'width' => 512,
                'height' => 512,
                'caption' => config('site.brand'),
            ],
            'image' => ['@id' => self::homeUrl().'#logo'],
            'foundingDate' => SiteIdentity::value('founding_year'),
            'address' => $country ? ['@type' => 'PostalAddress', 'addressCountry' => $country] : null,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'url' => route('pages.contact'),
                'availableLanguage' => 'English',
            ],
            'sameAs' => array_values(config('site.social')) ?: null,
        ], fn ($value) => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => self::websiteId(),
            'url' => self::homeUrl(),
            'name' => config('site.brand'),
            'description' => config('site.description'),
            'inLanguage' => config('site.locale'),
            'publisher' => ['@id' => self::organizationId()],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function webPage(Seo $seo): array
    {
        return array_filter([
            '@type' => $seo->pageType,
            '@id' => self::webPageId($seo->canonical),
            'url' => $seo->canonical,
            'name' => $seo->title,
            'description' => $seo->description,
            'inLanguage' => config('site.locale'),
            'isPartOf' => ['@id' => self::websiteId()],
            'publisher' => ['@id' => self::organizationId()],
            'breadcrumb' => count($seo->breadcrumbs) > 1 ? ['@id' => "{$seo->canonical}#breadcrumb"] : null,
            'primaryImageOfPage' => $seo->image ? [
                '@type' => 'ImageObject',
                'url' => $seo->image,
                'width' => $seo->imageSize()[0],
                'height' => $seo->imageSize()[1],
                'caption' => $seo->imageAlt,
            ] : null,
            'mainEntity' => $seo->mainEntityId ? ['@id' => $seo->mainEntityId] : null,
            'datePublished' => $seo->publishedTime,
            'dateModified' => $seo->modifiedTime,
            ...$seo->pageProperties,
        ], fn ($value) => $value !== null);
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $items, string $pageUrl): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id' => "{$pageUrl}#breadcrumb",
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['label'],
                'item' => $item['url'],
            ])->all(),
        ];
    }

    public static function applicationId(ToolPage $tool): string
    {
        return $tool->url().'#app';
    }

    /**
     * @return array<string, mixed>
     */
    public static function webApplication(ToolPage $tool): array
    {
        $figure = Figures::get($tool->figure);

        return array_filter([
            '@type' => 'WebApplication',
            '@id' => self::applicationId($tool),
            'name' => $tool->entityName(),
            'alternateName' => $tool->h1,
            'url' => $tool->url(),
            'description' => $tool->description,
            'applicationCategory' => 'MultimediaApplication',
            'applicationSubCategory' => $tool->content['schema_subcategory'] ?? 'Image compression',
            'operatingSystem' => $tool->content['schema_operating_system'] ?? 'Any (runs in a web browser)',
            'browserRequirements' => $tool->content['schema_browser_requirements'] ?? 'Requires JavaScript and a modern web browser.',
            'isAccessibleForFree' => true,
            'featureList' => $tool->featureList ?: null,
            'image' => $figure ? asset($figure['src']) : null,
            'inLanguage' => config('site.locale'),
            'dateModified' => $tool->updatedAt->toDateString(),
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
            'publisher' => ['@id' => self::organizationId()],
        ], fn ($value) => $value !== null);
    }

    /**
     * An ItemList of tool pages, used on hub pages.
     *
     * @param  Collection<string, ToolPage>  $tools
     * @return array<string, mixed>
     */
    public static function toolList(Collection $tools, string $pageUrl): array
    {
        return [
            '@type' => 'ItemList',
            '@id' => "{$pageUrl}#tools",
            'name' => config('site.brand').' image tools',
            'numberOfItems' => $tools->count(),
            'itemListElement' => $tools->values()->map(fn (ToolPage $tool, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $tool->entityName(),
                'url' => $tool->url(),
            ])->all(),
        ];
    }

    /**
     * Question nodes for a page whose main purpose is a list of questions and answers.
     *
     * @param  array<int, array{question: string, answer: string}>  $faqs
     * @return array<int, array<string, mixed>>
     */
    public static function questions(array $faqs): array
    {
        return collect($faqs)->map(fn (array $faq) => [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags($faq['answer']))],
        ])->all();
    }
}
