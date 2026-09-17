<?php

namespace App\Models;

use Database\Factories\ToolContentFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolContentField extends Model
{
    /** @use HasFactory<ToolContentFieldFactory> */
    use HasFactory;

    public const TYPES = [
        'text' => 'Text Input',
        'textarea' => 'Textarea',
        'html' => 'HTML',
        'url' => 'URL',
    ];

    protected $fillable = [
        'key',
        'type',
        'value',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Tool, $this>
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }
}
