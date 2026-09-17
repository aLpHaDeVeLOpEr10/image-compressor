<?php

namespace App\Http\Requests\Admin;

use App\Models\Tool;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreChildToolRequest extends FormRequest
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
        $parent = $this->parentTool();

        return [
            'locale' => [
                'required',
                Rule::in(array_keys(config('tools.languages'))),
                Rule::unique('tools', 'locale')->where('parent_id', $parent?->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'slug' => self::childSlugRules($this->isHomepage(), (string) $this->input('locale')),
            'status' => ['required', Rule::in(array_keys(Tool::STATUSES))],
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['required', 'string', 'max:300'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...self::childSlugMessages(),
            'locale.unique' => 'This tool already has a version in that language.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => self::prepareChildSlug($this->input('slug'), (string) $this->input('name'), $this->isHomepage())]);
    }

    /**
     * Slug rules for a language version. The homepage version may leave the slug empty to be served at /{locale}.
     *
     * @return array<int, mixed>
     */
    public static function childSlugRules(bool $isHomepage, string $locale, ?int $ignoreId = null): array
    {
        return [
            $isHomepage ? 'nullable' : 'required',
            'string',
            'max:100',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            Rule::unique('tools', 'slug')->where('locale', $locale)->whereNotNull('parent_id')->ignore($ignoreId),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function childSlugMessages(): array
    {
        return [
            'slug.required' => 'Enter a slug. It could not be generated from the title.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and single dashes.',
            'slug.unique' => 'Another tool in this language already uses this slug.',
        ];
    }

    /**
     * Trim slashes; generate a blank slug from the title, except for the homepage version, which stays at /{locale}.
     */
    public static function prepareChildSlug(mixed $slug, string $name, bool $isHomepage): ?string
    {
        $slug = trim(trim((string) $slug), '/');

        if ($slug !== '') {
            return $slug;
        }

        return $isHomepage ? null : (Str::slug($name) ?: null);
    }

    private function parentTool(): ?Tool
    {
        return Tool::whereNull('parent_id')->where('key', $this->route('toolKey'))->first();
    }

    private function isHomepage(): bool
    {
        return $this->route('toolKey') === config('tools.primary');
    }
}
