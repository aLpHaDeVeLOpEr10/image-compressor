<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_content_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->string('type', 20)->default('text');
            $table->text('value')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['tool_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_content_fields');
    }
};
