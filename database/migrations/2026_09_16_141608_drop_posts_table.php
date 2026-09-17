<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The blog was removed; drop its table where an earlier install created it.
     */
    public function up(): void
    {
        Schema::dropIfExists('posts');
    }

    /**
     * Run the migrations.
     */
    public function down(): void
    {
        //
    }
};
