<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Child tools are language versions of a parent tool. Slugs are unique per URL space (parents under /tools,
     * children under /{locale}), so the global slug index becomes a (locale, slug) index enforced by validation.
     */
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('tools')->cascadeOnDelete();
            $table->string('locale', 10)->nullable()->after('parent_id');

            $table->unique(['parent_id', 'locale']);
            $table->index(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropIndex(['locale', 'slug']);
            $table->dropUnique(['parent_id', 'locale']);
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('locale');
            $table->unique('slug');
        });
    }
};
