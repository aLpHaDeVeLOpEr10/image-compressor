<?php

namespace App\Tools;

use App\Models\Tool;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Keeps the database copy of tool content in step with the defaults in code, without touching edited values.
 */
class ToolContentSynchronizer
{
    public function __construct(private ToolRegistry $tools) {}

    /**
     * The database record for a tool: a configured parent tool is created with its default settings and content when
     * missing; child tools and tools added in the admin must already exist.
     */
    public function recordFor(string $toolKey): Tool
    {
        if (! in_array($toolKey, config('tools.pages'), true)) {
            return Tool::with(['contentFields', 'parent'])->where('key', $toolKey)->firstOrFail();
        }

        if (Tool::onlyTrashed()->where('key', $toolKey)->exists()) {
            throw (new ModelNotFoundException)->setModel(Tool::class, [$toolKey]);
        }

        $page = $this->tools->everything()->get($toolKey);

        $record = Tool::firstOrCreate(['key' => $toolKey], [
            'name' => $page->name,
            'slug' => $toolKey === config('tools.primary') ? null : $page->routeSlug(),
            'status' => Tool::STATUS_PUBLISHED,
            'meta_title' => $page->title,
            'meta_description' => $page->description,
        ]);

        if ($record->wasRecentlyCreated) {
            $this->appendFields($record, $this->tools->defaultFields($toolKey));
        }

        return $record->load('contentFields');
    }

    /**
     * Default content keys the record does not have yet, for example after new keys ship in code.
     *
     * @return array<string, array{type: string, value: string}>
     */
    public function missingFields(Tool $record): array
    {
        return array_diff_key($this->tools->defaultFields($record->definitionKey()), $record->contentFields->keyBy('key')->all());
    }

    /**
     * Append missing default keys after the existing ones and return how many were added.
     */
    public function addMissingFields(Tool $record): int
    {
        $missing = $this->missingFields($record);

        $this->appendFields($record, $missing);
        $record->load('contentFields');

        return count($missing);
    }

    /**
     * Create a tool in the admin: its record, its starter content and its content Blade view.
     *
     * @param  array{name: string, slug: string, status: string, meta_title: string, meta_description: string, blade_view: string}  $settings
     */
    public function createParent(array $settings, ToolViewGenerator $views): Tool
    {
        $tool = new Tool([...$settings, 'key' => $settings['slug']]);
        $tool->forceFill(['blade_view' => $settings['blade_view']])->save();

        $this->tools->flush();
        $this->appendFields($tool, $this->tools->defaultFields($tool->key));
        $this->tools->flush();

        if (! $views->exists($settings['blade_view'])) {
            $views->create($settings['blade_view'], $settings['name']);
        }

        return $tool->load('contentFields');
    }

    /**
     * Create a language version of a parent tool with a copy of every content key and value the parent has.
     *
     * @param  array{locale: string, name: string, slug: ?string, status: string, meta_title: string, meta_description: string}  $settings
     */
    public function createChild(Tool $parent, array $settings): Tool
    {
        $child = new Tool([...$settings, 'key' => "{$parent->key}--{$settings['locale']}"]);
        $child->forceFill(['parent_id' => $parent->id, 'locale' => $settings['locale']])->save();

        $child->contentFields()->createMany($parent->contentFields
            ->map(fn ($field) => ['key' => $field->key, 'type' => $field->type, 'value' => (string) $field->value, 'position' => $field->position])
            ->all());

        return $child->load(['contentFields', 'parent']);
    }

    /**
     * @param  array<string, array{type: string, value: string}>  $fields
     */
    private function appendFields(Tool $record, array $fields): void
    {
        $start = $record->contentFields()->exists() ? (int) $record->contentFields()->max('position') + 1 : 0;

        $record->contentFields()->createMany(collect($fields)
            ->map(fn (array $field, string $key) => ['key' => $key, 'type' => $field['type'], 'value' => $field['value']])
            ->values()
            ->map(fn (array $field, int $index) => [...$field, 'position' => $start + $index])
            ->all());
    }
}
