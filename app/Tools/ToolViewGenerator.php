<?php

namespace App\Tools;

use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Creates the content Blade view for a tool added in the admin, from a starter template that renders content keys.
 */
class ToolViewGenerator
{
    /**
     * The directory holding tool content views, e.g. resources/views/tools/content.
     */
    public function directory(): string
    {
        return (string) config('tools.content_views_path');
    }

    public function path(string $view): string
    {
        return $this->directory().DIRECTORY_SEPARATOR."{$view}.blade.php";
    }

    public function exists(string $view): bool
    {
        return is_file($this->path($view));
    }

    /**
     * Write the starter view and return its path. Never overwrites an existing file.
     *
     * @throws RuntimeException when the file exists or cannot be written
     */
    public function create(string $view, string $toolName): string
    {
        $path = $this->path($view);

        if (is_file($path)) {
            throw new RuntimeException("The view file [{$path}] already exists.");
        }

        File::ensureDirectoryExists($this->directory());

        if (file_put_contents($path, $this->stub($toolName), LOCK_EX) === false) {
            throw new RuntimeException("The view file [{$path}] could not be written. Check that the directory is writable.");
        }

        return $path;
    }

    private function stub(string $toolName): string
    {
        $toolName = str_replace(['{{', '}}', '--'], '', $toolName);

        return <<<BLADE
{{-- Content sections for the "{$toolName}" tool page, created from the admin.
     The heading, introduction, compressor, summary, FAQs and related tools are rendered around this view by
     tools/show.blade.php. Edit the text in the admin (Tools → {$toolName} → Edit); add new keys there and output
     them here with \$content['your_key']. Use {!! !!} only for keys of type HTML. --}}

@if (trim(\$content['body_heading'] ?? '') !== '' || trim(\$content['body_html'] ?? '') !== '')
    <x-content.section :title="\$content['body_heading'] ?? ''" id="about">
        {!! \$content['body_html'] ?? '' !!}
    </x-content.section>
@endif

BLADE;
    }
}
