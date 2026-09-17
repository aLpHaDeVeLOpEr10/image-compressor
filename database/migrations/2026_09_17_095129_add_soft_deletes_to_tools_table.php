<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trashed tools are hidden from the site and the admin lists until they are restored or deleted permanently.
     */
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
