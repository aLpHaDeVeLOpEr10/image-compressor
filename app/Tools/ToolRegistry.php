<?php

namespace App\Tools;

use App\Models\Tool;
use App\Models\ToolContentField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final class ToolRegistry
{
    /**
     * Content keys that replace the matching value in the tool's definition file.
     */
    public const OVERRIDABLE_FIELDS = ['h1', 'intro', 'summary', 'card_description'];

    /** @var Collection<string, ToolPage>|null */
    private ?Collection $tools = null;

    /** @var Collection<int, Tool>|null */
    private ?Collection $records = null;

    /** @var array<int, string>|null */
    private ?array $trashedKeys = null;

    /**
     * @param  array<int, string>  $keys
     */
    public function __construct(
        private readonly array $keys,
        private readonly string $contentPath,
        private readonly string $contentDefaultsPath,
    ) {}

    /**
     * Published parent tools in the site's main language, as listed on the public site.
     *
     * @return Collection<string, ToolPage>
     */
    public function all(): Collection
    {
        return $this->everything()->filter(fn (ToolPage $tool) => $tool->isPublished() && ! $tool->isChild());
    }

    /**
     * Every tool, including drafts and language versions. Each parent is followed by its children.
     *
     * @return Collection<string, ToolPage>
     */
    public function everything(): Collection
    {
        if ($this->tools !== null) {
            return $this->tools;
        }

        $records = $this->records();
        $parents = $records->whereNull('parent_id')->keyBy('key');
        $children = $records->whereNotNull('parent_id')->groupBy(fn (Tool $child) => $child->parent->key);

        return $this->tools = collect($this->keys())->flatMap(fn (string $key) => [
            $key => $this->build($key, $parents->get($key)),
            ...$children->get($key, collect())->mapWithKeys(fn (Tool $child) => [$child->key => $this->build($key, $child)])->all(),
        ]);
    }

    /**
     * Tool keys in display order: tools from config('tools.pages') first, then tools added in the admin. Tools in the
     * trash are left out.
     *
     * @return array<int, string>
     */
    public function keys(): array
    {
        return [
            ...array_values(array_diff($this->keys, $this->trashedKeys())),
            ...$this->records()->whereNull('parent_id')->whereNotIn('key', $this->keys)->sortBy('id')->pluck('key')->all(),
        ];
    }

    /**
     * Whether the tool was added in the admin rather than defined in config('tools.pages').
     */
    public function isCustom(string $key): bool
    {
        return ! in_array($key, $this->keys, true);
    }

    /**
     * Forget loaded tools, for example after a tool was added during the current request.
     */
    public function flush(): void
    {
        $this->tools = null;
        $this->records = null;
        $this->trashedKeys = null;
    }

    public function get(string $key): ToolPage
    {
        return $this->all()->get($key) ?? throw new InvalidArgumentException("Unknown tool [{$key}].");
    }

    public function primary(): ToolPage
    {
        return $this->everything()->get(config('tools.primary'));
    }

    /**
     * Published language versions of a parent tool.
     *
     * @return Collection<string, ToolPage>
     */
    public function translations(string $familyKey): Collection
    {
        return $this->everything()->filter(fn (ToolPage $tool) => $tool->parentKey === $familyKey && $tool->isPublished());
    }

    /**
     * The published version of a tool in the given language, falling back to the published parent.
     */
    public function localized(string $familyKey, ?string $locale = null): ?ToolPage
    {
        if ($locale !== null && $locale !== ToolPage::defaultLocale()) {
            $child = $this->translations($familyKey)->first(fn (ToolPage $tool) => $tool->locale === $locale);

            if ($child) {
                return $child;
            }
        }

        return $this->all()->get($familyKey);
    }

    /**
     * A tool added in the admin, served at /tools/{slug}, published or not.
     */
    public function findCustom(string $slug): ?ToolPage
    {
        return $this->everything()->first(fn (ToolPage $tool) => $tool->custom && ! $tool->isChild() && $tool->slug === $slug);
    }

    /**
     * The language version served at /{locale}/{slug}, or /{locale} for the homepage, published or not.
     */
    public function findChild(string $locale, ?string $slug): ?ToolPage
    {
        return $this->everything()->first(fn (ToolPage $tool) => $tool->isChild() && $tool->locale === $locale && $tool->slug === $slug);
    }

    /**
     * Site-wide header and footer text, edited on the primary tool or its version in the current language.
     *
     * @return array<string, string>
     */
    public function siteContent(): array
    {
        return ($this->localized(config('tools.primary'), app()->getLocale()) ?? $this->primary())->content;
    }

    /**
     * The tool's definition, without admin overrides: its content file for config tools, or its record for tools
     * added in the admin.
     *
     * @return array<string, mixed>
     */
    public function definition(string $key): array
    {
        if (! $this->isCustom($key)) {
            return require "{$this->contentPath}/{$key}.php";
        }

        $record = $this->records()->whereNull('parent_id')->firstWhere('key', $key)
            ?? throw new InvalidArgumentException("Unknown tool [{$key}].");

        return [
            'widget' => 'compressor',
            'category' => $record->category ?? array_key_first(config('tools.categories')),
            'name' => $record->name,
            'icon' => 'image',
            'title' => $record->meta_title,
            'description' => $record->meta_description,
            'h1' => $record->name,
            'intro' => $record->meta_description,
            'card_description' => $record->meta_description,
            'updated_at' => $record->created_at?->toDateString(),
            'widget_options' => [],
            'related_tools' => [],
            'custom' => true,
            'view' => $record->blade_view,
        ];
    }

    /**
     * The default content fields seeded into the admin editor, in display order: the tool's own content, then the
     * text shared by every tool page (widget, FAQ heading, ...). The primary tool also carries the site-wide header
     * and footer text.
     *
     * @return array<string, array{type: string, value: string}>
     */
    public function defaultFields(string $key): array
    {
        $fields = $this->toolDefaults($key) + $this->defaultsFile('_shared');

        if ($key === config('tools.primary')) {
            $fields += $this->defaultsFile('_site');
        }

        return $fields;
    }

    /**
     * Custom slugs saved in the admin, keyed by tool key. Read while routes are registered, so it must not depend on
     * the registry (content files call route()) and must tolerate a database that is missing or not yet migrated.
     *
     * @return array<string, string>
     */
    public static function routeSlugs(): array
    {
        try {
            return DB::table('tools')->whereNull('parent_id')->whereNull('deleted_at')->whereNotNull('slug')->pluck('slug', 'key')->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Tools grouped by category label, in the order defined in config('tools.categories').
     *
     * @return Collection<string, Collection<string, ToolPage>>
     */
    public function grouped(): Collection
    {
        return collect(config('tools.categories'))
            ->mapWithKeys(fn (string $label, string $category) => [
                $label => $this->all()->filter(fn (ToolPage $tool) => $tool->category === $category),
            ])
            ->filter(fn (Collection $tools) => $tools->isNotEmpty());
    }

    /**
     * @param  array<int, string>  $keys
     * @return Collection<string, ToolPage>
     */
    public function only(array $keys): Collection
    {
        return collect($keys)
            ->filter(fn (string $key) => $this->all()->has($key))
            ->mapWithKeys(fn (string $key) => [$key => $this->all()->get($key)]);
    }

    /**
     * @param  string  $key  the config key whose definition and defaults are used
     * @param  Tool|null  $record  the parent's record, or a child record for a language version
     */
    private function build(string $key, ?Tool $record): ToolPage
    {
        $data = $this->definition($key);
        $isPrimary = $key === config('tools.primary');

        $defaults = $this->defaultsFile('_shared') + ($isPrimary ? $this->defaultsFile('_site') : []);
        $values = array_map(fn (array $field) => $field['value'], array_merge($defaults, $this->toolDefaults($key)));
        $defaultValues = array_map(ToolContent::resolve(...), $values);

        foreach ($record?->contentFields ?? [] as $field) {
            /** @var ToolContentField $field */
            $values[$field->key] = (string) $field->value;
        }

        $content = array_map(ToolContent::resolve(...), $values);

        foreach (self::OVERRIDABLE_FIELDS as $field) {
            if (array_key_exists($field, $content) && ($field === 'summary' || trim($content[$field]) !== '')) {
                $data[$field] = $content[$field];
            } else {
                $data[$field] ??= $defaultValues[$field] ?? '';
            }
        }

        if ($features = ToolContent::list($content, 'feature')) {
            $data['feature_list'] = $features;
        }

        if ($faqs = ToolContent::list($content, 'faq', ['question', 'answer'])) {
            $data['faqs'] = $faqs;
        }

        $data['content'] = $content;

        if ($record !== null) {
            $fileUpdatedAt = $data['updated_at'] ?? config('site.pages_updated_at');

            $data = [
                ...$data,
                'name' => $record->name,
                'title' => $record->meta_title,
                'description' => $record->meta_description,
                'status' => $record->status,
                'slug' => $record->slug,
                'image' => $record->og_image ? [
                    'src' => "storage/{$record->og_image}",
                    'width' => $record->og_image_width ?? 1200,
                    'height' => $record->og_image_height ?? 630,
                    'alt' => $record->name,
                ] : null,
                'updated_at' => $record->content_updated_at?->gt($fileUpdatedAt) ? $record->content_updated_at->toDateString() : $fileUpdatedAt,
                'locale' => $record->locale,
                'parent_key' => $record->isChild() ? $key : null,
            ];
        }

        return ToolPage::fromArray($record?->key ?? $key, $data);
    }

    /**
     * A tool's own default content: its defaults file when one exists, otherwise the editable text from its definition.
     *
     * @return array<string, array{type: string, value: string}>
     */
    private function toolDefaults(string $key): array
    {
        if (is_file("{$this->contentDefaultsPath}/{$key}.php")) {
            return $this->defaultsFile($key);
        }

        $definition = $this->definition($key);

        $fields = [
            'h1' => ['type' => 'text', 'value' => (string) ($definition['h1'] ?? '')],
            'intro' => ['type' => 'textarea', 'value' => (string) ($definition['intro'] ?? '')],
            'summary' => ['type' => 'textarea', 'value' => (string) ($definition['summary'] ?? '')],
            'card_description' => ['type' => 'textarea', 'value' => (string) ($definition['card_description'] ?? '')],
        ];

        if ($definition['custom'] ?? false) {
            $fields['body_heading'] = ['type' => 'text', 'value' => "About the {$definition['name']}"];
            $fields['body_html'] = ['type' => 'html', 'value' => '<p>'.e($definition['description']).'</p>'];
        }

        return $fields;
    }

    /**
     * Keys of parent tools in the trash.
     *
     * @return array<int, string>
     */
    private function trashedKeys(): array
    {
        return $this->trashedKeys ??= Tool::onlyTrashed()->whereNull('parent_id')->pluck('key')->all();
    }

    /**
     * @return Collection<int, Tool>
     */
    private function records(): Collection
    {
        return $this->records ??= Tool::with(['contentFields', 'parent'])->orderBy('locale')->get();
    }

    /**
     * @return array<string, array{type: string, value: string}>
     */
    private function defaultsFile(string $name): array
    {
        static $cache = [];

        $path = "{$this->contentDefaultsPath}/{$name}.php";

        return $cache[$path] ??= collect(is_file($path) ? require $path : [])
            ->map(fn (string|array $field) => is_array($field)
                ? ['type' => $field['type'] ?? 'text', 'value' => (string) ($field['value'] ?? '')]
                : ['type' => 'text', 'value' => $field])
            ->all();
    }
}
