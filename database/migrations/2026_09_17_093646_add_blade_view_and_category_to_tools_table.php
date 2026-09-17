<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tools added in the admin are defined by their record instead of a config entry: the content Blade view
     * (resources/views/tools/content/{blade_view}.blade.php) and the category they are listed under.
     */
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->string('blade_view', 100)->nullable()->after('status');
            $table->string('category', 50)->nullable()->after('blade_view');
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropColumn(['blade_view', 'category']);
        });
    }
};
