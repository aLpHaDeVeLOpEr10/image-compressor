<?php

namespace App\Http\Requests\Admin;

use App\Models\Tool;
use App\Tools\ToolPage;
use App\Tools\ToolRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreToolRequest extends FormRequest
{
    public const TYPE_PARENT = 'parent';

    public const TYPE_CHILD = 'child';

    private const SLUG_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'type' => ['required', Rule::in([self::TYPE_PARENT, self::TYPE_CHILD])],
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(array_keys(Tool::STATUSES))],
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['required', 'string', 'max:300'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($this->isChild()) {
            $parent = Tool::whereNull('parent_id')->where('key', $this->input('parent'))->first();

            return [
                ...$rules,
                'parent' => ['required', Rule::in($this->parentKeys())],
                'locale' => [
                    'required',
                    Rule::in(array_keys(config('tools.languages'))),
                    Rule::unique('tools', 'locale')->where('parent_id', $parent?->id ?? 0),
                ],
                'slug' => StoreChildToolRequest::childSlugRules($this->input('parent') === config('tools.primary'), (string) $this->input('locale')),
            ];
        }

        return [
            ...$rules,
            'slug' => ['required', 'string', 'max:100', 'regex:'.self::SLUG_PATTERN, Rule::notIn($this->reservedSlugs()), Rule::unique('tools', 'key')],
            'blade_view' => ['required', 'string', 'max:100', 'regex:'.self::SLUG_PATTERN],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...StoreChildToolRequest::childSlugMessages(),
            'slug.not_in' => 'Another tool already uses this slug.',
            'slug.unique' => $this->isChild() ? 'Another tool in this language already uses this slug.' : 'Another tool already uses this slug.',
            'parent.required' => 'Select the parent tool.',
            'parent.in' => 'Select a valid parent tool.',
            'locale.required' => 'Select a language for the child tool.',
            'locale.unique' => 'This tool already has a version in that language.',
            'blade_view.required' => 'Enter a Blade file name. It could not be generated from the slug.',
            'blade_view.regex' => 'The Blade file name may only contain lowercase letters, numbers and single dashes.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->isChild()) {
            $this->merge(['slug' => StoreChildToolRequest::prepareChildSlug($this->input('slug'), (string) $this->input('name'), $this->input('parent') === config('tools.primary'))]);

            return;
        }

        $slug = trim(trim((string) $this->input('slug')), '/');
        $slug = $slug === '' ? Str::slug((string) $this->input('name')) : $slug;
        $view = Str::of((string) $this->input('blade_view'))->trim()->replaceEnd('.blade.php', '')->trim('/')->toString();

        $this->merge([
            'slug' => $slug,
            'blade_view' => $view === '' ? $slug : $view,
        ]);
    }

    public function isChild(): bool
    {
        return $this->input('type') === self::TYPE_CHILD;
    }

    /**
     * @return array<int, string>
     */
    private function parentKeys(): array
    {
        return app(ToolRegistry::class)->everything()->reject(fn (ToolPage $tool) => $tool->isChild())->keys()->all();
    }

    /**
     * Paths under /tools already taken by a tool, including config keys whose routes are registered at boot.
     *
     * @return array<int, string>
     */
    private function reservedSlugs(): array
    {
        return app(ToolRegistry::class)->everything()
            ->reject(fn (ToolPage $tool) => $tool->isChild())
            ->map(fn (ToolPage $tool) => $tool->routeSlug())
            ->merge(config('tools.pages'))
            ->unique()
            ->values()
            ->all();
    }
}
