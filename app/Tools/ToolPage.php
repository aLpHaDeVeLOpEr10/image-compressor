<?php

namespace App\Tools;

use Illuminate\Support\Carbon;

final readonly class ToolPage
{
    /**
     * @param  array<string, mixed>  $widgetOptions
     * @param  array<int, array{question: string, answer: string}>  $faqs
     * @param  array<int, string>  $relatedTools
     * @param  array<int, string>  $featureList
     * @param  array<string, string>  $content  key/value content managed in the admin, exposed to Blade as $content
     * @param  array{src: string, width: int, height: int, alt: string}|null  $image  social share image uploaded in the admin
     */
    public function __construct(
        public string $key,
        public string $widget,
        public string $category,
        public string $name,
        public string $title,
        public string $description,
        public string $h1,
        public string $intro,
        public string $summary,
        public string $cardDescription,
        public string $icon,
        public Carbon $updatedAt,
        public ?string $figure = null,
        public array $featureList = [],
        public array $widgetOptions = [],
        public array $faqs = [],
        public array $relatedTools = [],
        public string $status = 'published',
        public ?string $slug = null,
        public array $content = [],
        public ?array $image = null,
        public ?string $locale = null,
        public ?string $parentKey = null,
        public bool $custom = false,
        public ?string $view = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(string $key, array $data): self
    {
        return new self(
            key: $key,
            widget: $data['widget'],
            category: $data['category'] ?? 'general',
            name: $data['name'],
            title: $data['title'],
            description: $data['description'],
            h1: $data['h1'],
            intro: $data['intro'],
            summary: $data['summary'] ?? '',
            cardDescription: $data['card_description'],
            icon: $data['icon'] ?? 'image',
            updatedAt: Carbon::parse($data['updated_at'] ?? config('site.pages_updated_at')),
            figure: $data['figure'] ?? null,
            featureList: $data['feature_list'] ?? [],
            widgetOptions: $data['widget_options'] ?? [],
            faqs: $data['faqs'] ?? [],
            relatedTools: $data['related_tools'] ?? [],
            status: $data['status'] ?? 'published',
            slug: $data['slug'] ?? null,
            content: $data['content'] ?? [],
            image: $data['image'] ?? null,
            locale: $data['locale'] ?? null,
            parentKey: $data['parent_key'] ?? null,
            custom: (bool) ($data['custom'] ?? false),
            view: $data['view'] ?? null,
        );
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * The path segment after the tools prefix; the config key unless a custom slug was saved in the admin.
     */
    public function routeSlug(): string
    {
        return $this->slug ?? $this->key;
    }

    public function isPrimary(): bool
    {
        return $this->key === config('tools.primary');
    }

    /**
     * A language version of a parent tool, served at /{locale}/{slug}.
     */
    public function isChild(): bool
    {
        return $this->parentKey !== null;
    }

    /**
     * The config key of the tool this page is (a language version of).
     */
    public function familyKey(): string
    {
        return $this->parentKey ?? $this->key;
    }

    /**
     * Whether this is the homepage tool or a language version of it.
     */
    public function isHomepage(): bool
    {
        return $this->familyKey() === config('tools.primary');
    }

    /**
     * The page language: the child's locale, or the site language for parent tools.
     */
    public function language(): string
    {
        return $this->locale ?? self::defaultLocale();
    }

    /**
     * The site's main language, even while a language version has switched site.locale for its request.
     */
    public static function defaultLocale(): string
    {
        return (string) (config('site.default_locale') ?? config('site.locale'));
    }

    /**
     * The entity name used in structured data, e.g. "CompressPix JPG Compressor".
     */
    public function entityName(): string
    {
        $brand = config('site.brand');

        return str_starts_with($this->name, $brand) ? $this->name : "{$brand} {$this->name}";
    }

    /**
     * The primary tool is served by the homepage; every other tool lives under the tools prefix.
     */
    public function routeName(): string
    {
        return match (true) {
            $this->isChild() => 'tools.localized',
            $this->custom => 'tools.custom',
            $this->isPrimary() => 'home',
            default => "tools.{$this->key}",
        };
    }

    public function url(): string
    {
        if ($this->isChild()) {
            return route('tools.localized', array_filter(['locale' => $this->locale, 'slug' => $this->slug]));
        }

        if ($this->custom) {
            return route('tools.custom', ['slug' => $this->slug]);
        }

        return route($this->routeName());
    }

    public function contentView(): string
    {
        return 'tools.content.'.($this->view ?? $this->familyKey());
    }

    public function widgetView(): string
    {
        return "tools.widgets.{$this->widget}";
    }
}
