<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('name', 100);
            $table->string('slug', 100)->nullable()->unique();
            $table->string('status', 20)->default('published');
            $table->string('meta_title', 120);
            $table->string('meta_description', 300);
            $table->string('og_image')->nullable();
            $table->unsignedSmallInteger('og_image_width')->nullable();
            $table->unsignedSmallInteger('og_image_height')->nullable();
            $table->timestamp('content_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
