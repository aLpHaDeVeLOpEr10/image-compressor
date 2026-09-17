<?php

namespace App\Models;

use Database\Factories\ToolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Database overrides for a tool page. A parent tool's `key` matches a key in config('tools.pages'); a child tool is a
 * language version of its parent, with its own locale, slug, settings and a copy of the parent's content keys.
 */
class Tool extends Model
{
    /** @use HasFactory<ToolFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_DRAFT = 'draft';

    public const STATUSES = [
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_DRAFT => 'Draft',
    ];

    protected $fillable = [
        'key',
        'name',
        'slug',
        'status',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'og_image_width' => 'integer',
            'og_image_height' => 'integer',
            'content_updated_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ToolContentField, $this>
     */
    public function contentFields(): HasMany
    {
        return $this->hasMany(ToolContentField::class)->orderBy('position');
    }

    /**
     * @return BelongsTo<Tool, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'parent_id')->withTrashed();
    }

    /**
     * @return HasMany<Tool, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Tool::class, 'parent_id')->orderBy('locale');
    }

    /**
     * Tools shown in the trash: trashed parents, and trashed language versions whose parent is not in the trash
     * (versions trashed together with their parent are restored with it).
     *
     * @return Collection<int, Tool>
     */
    public static function trashListing(): Collection
    {
        return static::onlyTrashed()
            ->with('parent')
            ->withCount(['children as trashed_children_count' => fn ($query) => $query->onlyTrashed()])
            ->latest('deleted_at')
            ->get()
            ->reject(fn (Tool $tool) => $tool->isChild() && $tool->parent?->trashed())
            ->values();
    }

    public function isChild(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * The config key whose definition, widget and default content this tool uses.
     */
    public function definitionKey(): string
    {
        return $this->isChild() ? $this->parent->key : $this->key;
    }
}
