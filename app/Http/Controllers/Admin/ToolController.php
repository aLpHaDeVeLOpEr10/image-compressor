<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreChildToolRequest;
use App\Http\Requests\Admin\StoreToolRequest;
use App\Http\Requests\Admin\UpdateToolRequest;
use App\Models\Tool;
use App\Models\ToolContentField;
use App\Tools\ToolContentSynchronizer;
use App\Tools\ToolPage;
use App\Tools\ToolRegistry;
use App\Tools\ToolViewGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ToolController extends Controller
{
    public function index(Request $request, ToolRegistry $tools): View
    {
        $categories = config('tools.categories');
        $category = array_key_exists((string) $request->query('category'), $categories) ? (string) $request->query('category') : null;
        $search = trim((string) $request->query('search'));
        $parents = $tools->everything()->reject(fn (ToolPage $tool) => $tool->isChild());

        $filteredTools = $parents
            ->when($category, fn ($all) => $all->filter(fn (ToolPage $tool) => $tool->category === $category))
            ->when($search !== '', fn ($all) => $all->filter(fn (ToolPage $tool) => Str::contains(
                "{$tool->name} {$tool->key} {$tool->cardDescription}",
                $search,
                ignoreCase: true,
            )));

        return view('admin.tools.index', [
            'tools' => $filteredTools,
            'children' => $tools->everything()->filter(fn (ToolPage $tool) => $tool->isChild())->groupBy(fn (ToolPage $tool) => $tool->parentKey),
            'totalTools' => $parents->count(),
            'categoryCounts' => $parents->countBy('category'),
            'categories' => $categories,
            'category' => $category,
            'search' => $search,
            'navigationKeys' => array_keys(config('tools.navigation')),
            'languages' => config('tools.languages'),
        ]);
    }

    public function create(Request $request, ToolRegistry $tools, ToolViewGenerator $views): View
    {
        $parents = $tools->everything()->reject(fn (ToolPage $tool) => $tool->isChild());

        return view('admin.tools.create', [
            'parents' => $parents,
            'selectedParent' => $parents->has((string) $request->query('parent')) ? (string) $request->query('parent') : null,
            'usedLocales' => $tools->everything()
                ->filter(fn (ToolPage $tool) => $tool->isChild())
                ->groupBy(fn (ToolPage $tool) => $tool->parentKey)
                ->map(fn ($children) => $children->pluck('locale')->values()),
            'languages' => config('tools.languages'),
            'statuses' => Tool::STATUSES,
            'primaryKey' => config('tools.primary'),
            'viewsDirectory' => $this->relativePath($views->directory()),
        ]);
    }

    public function store(StoreToolRequest $request, ToolContentSynchronizer $synchronizer, ToolViewGenerator $views): RedirectResponse
    {
        $settings = $request->safe()->only(['name', 'slug', 'status', 'meta_title', 'meta_description']);

        if ($request->isChild()) {
            $parent = $synchronizer->recordFor($request->validated('parent'));

            $tool = DB::transaction(function () use ($request, $synchronizer, $parent, $settings) {
                $child = $synchronizer->createChild($parent, [...$settings, 'locale' => $request->validated('locale')]);
                $this->applyImage($request, $child);
                $child->save();

                return $child;
            });

            $language = config("tools.languages.{$tool->locale}");

            return to_route('admin.tools.edit', $tool->key)
                ->with('status', "{$language} version of {$parent->name} created with {$tool->contentFields->count()} content keys copied. Translate the values below.");
        }

        $viewExisted = $views->exists($request->validated('blade_view'));

        $tool = DB::transaction(function () use ($request, $synchronizer, $views, $settings) {
            $tool = $synchronizer->createParent([...$settings, ...$request->safe()->only(['blade_view'])], $views);
            $this->applyImage($request, $tool);
            $tool->save();

            return $tool;
        });

        $file = $this->relativePath($views->path($tool->blade_view));

        return to_route('admin.tools.edit', $tool->key)
            ->with('status', $viewExisted
                ? "{$tool->name} was created. It uses the existing Blade file {$file}, which was not changed."
                : "{$tool->name} was created with the Blade file {$file}.");
    }

    public function edit(ToolRegistry $tools, ToolContentSynchronizer $synchronizer, string $toolKey): View
    {
        $record = $synchronizer->recordFor($toolKey);

        return view('admin.tools.edit', [
            'record' => $record,
            'tool' => $tools->everything()->get($toolKey),
            'isPrimary' => $toolKey === config('tools.primary'),
            'isChild' => $record->isChild(),
            'isHomepage' => $record->definitionKey() === config('tools.primary'),
            'isCustom' => $tools->isCustom($record->definitionKey()),
            'bladeFile' => $this->relativePath(app(ToolViewGenerator::class)->path($tools->definition($record->definitionKey())['view'] ?? $record->definitionKey())),
            'translations' => $record->isChild() ? collect() : $tools->everything()->filter(fn (ToolPage $tool) => $tool->parentKey === $toolKey),
            'languages' => config('tools.languages'),
            'fields' => $record->contentFields->map(fn (ToolContentField $field) => $field->only(['key', 'type', 'value']))->all(),
            'missingDefaultCount' => count($synchronizer->missingFields($record)),
            'fieldTypes' => ToolContentField::TYPES,
            'statuses' => Tool::STATUSES,
        ]);
    }

    public function update(UpdateToolRequest $request, ToolContentSynchronizer $synchronizer, string $toolKey): RedirectResponse
    {
        $record = $synchronizer->recordFor($toolKey);
        $previousSlug = $record->slug;
        $previousImage = $record->og_image;

        DB::transaction(function () use ($request, $record) {
            $record->fill($request->safe()->only(['name', 'slug', 'status', 'meta_title', 'meta_description']));
            $this->applyImage($request, $record);
            $record->forceFill(['content_updated_at' => now()])->save();

            $record->contentFields()->delete();
            $record->contentFields()->createMany(collect($request->validated('fields', []))
                ->values()
                ->map(fn (array $field, int $position) => [...$field, 'value' => $field['value'] ?? '', 'position' => $position])
                ->all());
        });

        if ($previousImage && $previousImage !== $record->og_image) {
            Storage::disk('public')->delete($previousImage);
        }

        if (! $record->isChild() && $previousSlug !== $record->slug && app()->routesAreCached()) {
            Artisan::call('route:clear');
        }

        return to_route('admin.tools.edit', $toolKey)->with('status', "{$record->name} was saved.");
    }

    /**
     * Move a tool to the trash, together with its language versions. The homepage tool cannot be trashed.
     */
    public function destroy(ToolContentSynchronizer $synchronizer, string $toolKey): RedirectResponse
    {
        $record = $synchronizer->recordFor($toolKey);

        abort_if($record->key === config('tools.primary'), 403);

        $trashedAt = now();
        $record->children()->update(['deleted_at' => $trashedAt]);
        $record->forceFill(['deleted_at' => $trashedAt])->saveQuietly();

        $status = "{$record->name} was moved to the trash.";

        if ($record->isChild()) {
            return to_route('admin.tools.edit', $record->parent->key)->with('status', $status);
        }

        return to_route('admin.tools.index')->with('status', $status);
    }

    public function trash(): View
    {
        return view('admin.tools.trash', [
            'trashedTools' => Tool::trashListing(),
            'languages' => config('tools.languages'),
            'builtInKeys' => config('tools.pages'),
            'toolsPrefix' => config('tools.prefix'),
        ]);
    }

    /**
     * Restore a tool from the trash, with the language versions that were trashed together with it.
     */
    public function restore(string $toolKey): RedirectResponse
    {
        $record = Tool::onlyTrashed()->with('parent')->where('key', $toolKey)->firstOrFail();

        if ($record->isChild() && $record->parent->trashed()) {
            return to_route('admin.tools.trash')->withErrors(['restore' => "Restore {$record->parent->name} first; this language version belongs to it."]);
        }

        DB::transaction(function () use ($record) {
            if (! $record->isChild()) {
                $record->children()->onlyTrashed()->where('deleted_at', $record->deleted_at)->update(['deleted_at' => null]);
            }

            $record->restore();
        });

        app(ToolRegistry::class)->flush();

        return to_route('admin.tools.trash')->with('status', "{$record->name} was restored.");
    }

    /**
     * Permanently delete a trashed language version, or a trashed tool added in the admin with its language versions.
     * Built-in tools are defined in code and can only be restored. Blade files are kept.
     */
    public function forceDestroy(ToolRegistry $tools, ToolViewGenerator $views, string $toolKey): RedirectResponse
    {
        $record = Tool::onlyTrashed()->with('parent')->where('key', $toolKey)->firstOrFail();

        abort_unless($record->isChild() || $tools->isCustom($record->key), 403);

        $images = $record->children()->withTrashed()->pluck('og_image')->push($record->og_image)->filter()->all();

        DB::transaction(function () use ($record) {
            $record->children()->withTrashed()->get()->each->forceDelete();
            $record->forceDelete();
        });

        Storage::disk('public')->delete($images);

        $status = "{$record->name} was deleted permanently.";

        if (! $record->isChild() && $record->blade_view) {
            $status .= ' Its Blade file '.$this->relativePath($views->path($record->blade_view)).' was kept.';
        }

        return to_route('admin.tools.trash')->with('status', $status);
    }

    /**
     * Add default content keys that the saved record does not have yet, for example after new keys ship in code.
     */
    public function syncDefaults(ToolContentSynchronizer $synchronizer, string $toolKey): RedirectResponse
    {
        $added = $synchronizer->addMissingFields($synchronizer->recordFor($toolKey));

        return to_route('admin.tools.edit', $toolKey)->with('status', $added.' default '.Str::plural('key', $added).' added.');
    }

    public function createChild(ToolContentSynchronizer $synchronizer, string $toolKey): View
    {
        $parent = $synchronizer->recordFor($toolKey);
        abort_if($parent->isChild(), 404);
        $usedLocales = $parent->children()->pluck('locale')->all();

        return view('admin.tools.create-child', [
            'parent' => $parent,
            'isHomepage' => $toolKey === config('tools.primary'),
            'languages' => array_diff_key(config('tools.languages'), array_flip($usedLocales)),
            'statuses' => Tool::STATUSES,
        ]);
    }

    public function storeChild(StoreChildToolRequest $request, ToolContentSynchronizer $synchronizer, string $toolKey): RedirectResponse
    {
        $parent = $synchronizer->recordFor($toolKey);
        abort_if($parent->isChild(), 404);

        $child = DB::transaction(function () use ($request, $synchronizer, $parent) {
            $child = $synchronizer->createChild($parent, $request->safe()->only(['locale', 'name', 'slug', 'status', 'meta_title', 'meta_description']));
            $this->applyImage($request, $child);
            $child->save();

            return $child;
        });

        $language = config("tools.languages.{$child->locale}");

        return to_route('admin.tools.edit', $child->key)
            ->with('status', "{$language} version created with {$child->contentFields->count()} content keys copied from {$parent->name}. Translate the values below.");
    }

    private function relativePath(string $path): string
    {
        return str_replace('\\', '/', ltrim(str_replace(base_path(), '', $path), '\\/'));
    }

    /**
     * Store a newly uploaded share image, or clear the current one when requested.
     */
    private function applyImage(Request $request, Tool $record): void
    {
        if ($request->hasFile('image')) {
            [$width, $height] = getimagesize($request->file('image')->getRealPath()) ?: [null, null];
            $record->forceFill([
                'og_image' => $request->file('image')->store('tool-images', 'public'),
                'og_image_width' => $width,
                'og_image_height' => $height,
            ]);
        } elseif ($request->boolean('remove_image')) {
            $record->forceFill(['og_image' => null, 'og_image_width' => null, 'og_image_height' => null]);
        }
    }
}
