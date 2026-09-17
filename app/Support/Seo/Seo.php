<?php

namespace App\Support\Seo;

final class Seo
{
    public const INDEX = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

    /**
     * Language versions of the page for hreflang links, as language (or x-default) => URL.
     *
     * @var array<string, string>
     */
    public array $alternates = [];

    /**
     * @param  array<int, array{label: string, url: string}>  $breadcrumbs
     * @param  array<int, array<string, mixed>>  $schema  additional structured data nodes for the page graph
     * @param  array<string, mixed>  $pageProperties  extra properties merged into the WebPage node
     */
    public function __construct(
        public string $title,
        public string $description,
        public ?string $canonical = null,
        public string $robots = self::INDEX,
        public ?string $image = null,
        public ?string $imageAlt = null,
        public ?int $imageWidth = null,
        public ?int $imageHeight = null,
        public string $type = 'website',
        public string $pageType = 'WebPage',
        public ?string $mainEntityId = null,
        public array $breadcrumbs = [],
        public array $schema = [],
        public array $pageProperties = [],
        public ?string $publishedTime = null,
        public ?string $modifiedTime = null,
    ) {}

    public static function make(string $title, string $description): self
    {
        return new self($title, $description, canonical: url()->current());
    }

    public static function fallback(): self
    {
        return new self(
            title: config('site.brand'),
            description: config('site.description'),
            canonical: url()->current(),
        );
    }

    /**
     * The document title, with the brand appended only when it still fits within 60 characters so the page's
     * own keywords are not truncated in search results.
     */
    public function fullTitle(): string
    {
        $brand = config('site.brand');
        $branded = "{$this->title} | {$brand}";

        return str_contains($this->title, $brand) || mb_strlen($branded) > 60 ? $this->title : $branded;
    }

    public function imageUrl(): string
    {
        return $this->image ?? asset(config('site.assets.og_image'));
    }

    public function imageAlt(): string
    {
        return $this->imageAlt ?? config('site.brand').' – free online image compressor for JPG, PNG and WebP';
    }

    /**
     * @return array{0: int, 1: int}
     */
    public function imageSize(): array
    {
        return $this->image ? [$this->imageWidth ?? 1200, $this->imageHeight ?? 675] : [1200, 630];
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $breadcrumbs
     */
    public function withBreadcrumbs(array $breadcrumbs): self
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    /**
     * @param  array<string, mixed>  ...$schema
     */
    public function withSchema(array ...$schema): self
    {
        array_push($this->schema, ...$schema);

        return $this;
    }

    /**
     * @param  array{src: string, width: int, height: int, alt: string}|null  $figure
     */
    public function withImage(?array $figure): self
    {
        if ($figure !== null) {
            $this->image = asset($figure['src']);
            $this->imageAlt = $figure['alt'];
            $this->imageWidth = $figure['width'];
            $this->imageHeight = $figure['height'];
        }

        return $this;
    }

    public function asPage(string $pageType, ?string $mainEntityId = null): self
    {
        $this->pageType = $pageType;
        $this->mainEntityId = $mainEntityId ?? $this->mainEntityId;

        return $this;
    }

    public function noindex(): self
    {
        $this->robots = 'noindex, follow';

        return $this;
    }

    public function isIndexable(): bool
    {
        return str_starts_with($this->robots, 'index');
    }

    public function asArticle(?string $publishedTime, ?string $modifiedTime): self
    {
        $this->type = 'article';
        $this->publishedTime = $publishedTime;
        $this->modifiedTime = $modifiedTime;

        return $this;
    }

    public function updatedAt(?string $modifiedTime): self
    {
        $this->modifiedTime = $modifiedTime;

        return $this;
    }

    /**
     * The complete JSON-LD graph for the page: organization, website, web page, breadcrumbs and page-specific nodes.
     *
     * @return array<string, mixed>|null
     */
    public function jsonLd(): ?array
    {
        if ($this->canonical === null) {
            return null;
        }

        $graph = [
            StructuredData::organization(),
            StructuredData::website(),
            StructuredData::webPage($this),
        ];

        if (count($this->breadcrumbs) > 1) {
            $graph[] = StructuredData::breadcrumbs($this->breadcrumbs, $this->canonical);
        }

        return ['@context' => 'https://schema.org', '@graph' => [...$graph, ...$this->schema]];
    }
}
