<?php

namespace App\Http\Requests\Admin;

use App\Models\Tool;
use App\Models\ToolContentField;
use App\Tools\ToolPage;
use App\Tools\ToolRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $isPrimary = $this->isPrimaryTool();
        $child = $this->childTool();

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => match (true) {
                $child !== null => StoreChildToolRequest::childSlugRules($child->parent->key === config('tools.primary'), (string) $child->locale, $child->id),
                $isPrimary => ['prohibited'],
                default => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::notIn($this->otherToolSlugs())],
            },
            'status' => ['required', Rule::in($isPrimary ? [Tool::STATUS_PUBLISHED] : array_keys(Tool::STATUSES))],
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['required', 'string', 'max:300'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['boolean'],
            'fields' => ['array', 'max:1000'],
            'fields.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct'],
            'fields.*.type' => ['required', Rule::in(array_keys(ToolContentField::TYPES))],
            'fields.*.value' => ['nullable', 'string', 'max:65535'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...($this->childTool() ? StoreChildToolRequest::childSlugMessages() : []),
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and single dashes.',
            'slug.not_in' => 'Another tool already uses this slug.',
            'slug.prohibited' => 'The homepage tool is always served at /.',
            'status.in' => 'The homepage tool must stay published.',
            'fields.*.key.required' => 'Every field needs a key.',
            'fields.*.key.regex' => 'Keys must start with a letter and use only lowercase letters, numbers and underscores.',
            'fields.*.key.distinct' => 'This key is used more than once.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // The editor posts fields as one JSON value so large pages are not cut off by PHP's max_input_vars.
        $fieldsJson = $this->input('fields_json');
        $fields = is_string($fieldsJson) ? json_decode($fieldsJson, true) : $this->input('fields', []);

        $this->merge([
            'fields' => is_array($fields) ? array_values($fields) : [],
            'remove_image' => $this->boolean('remove_image'),
        ]);

        if ($child = $this->childTool()) {
            $this->merge(['slug' => StoreChildToolRequest::prepareChildSlug($this->input('slug'), (string) $this->input('name'), $child->parent->key === config('tools.primary'))]);

            return;
        }

        if ($this->isPrimaryTool()) {
            return;
        }

        $slug = trim(trim((string) $this->input('slug')), '/');

        $this->merge(['slug' => $slug === '' ? Str::slug((string) $this->input('name')) : $slug]);
    }

    /**
     * The language version being updated, or null for a parent tool.
     */
    private function childTool(): ?Tool
    {
        return once(fn () => Tool::with('parent')->whereNotNull('parent_id')->where('key', $this->route('toolKey'))->first());
    }

    private function isPrimaryTool(): bool
    {
        return $this->route('toolKey') === config('tools.primary');
    }

    /**
     * @return array<int, string>
     */
    private function otherToolSlugs(): array
    {
        return app(ToolRegistry::class)->everything()
            ->except([$this->route('toolKey'), config('tools.primary')])
            ->reject(fn (ToolPage $tool) => $tool->isChild())
            ->map(fn (ToolPage $tool) => $tool->routeSlug())
            ->merge(array_diff(config('tools.pages'), [$this->route('toolKey')]))
            ->unique()
            ->values()
            ->all();
    }
}
