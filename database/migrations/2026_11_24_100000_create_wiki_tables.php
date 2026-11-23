<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wiki_categories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            // Only empty categories may be deleted, the foreign keys back that up
            $table->foreignUlid('parent_id')
                ->nullable()
                ->constrained('wiki_categories')
                ->restrictOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('wiki_pages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('category_id')
                ->nullable()
                ->constrained('wiki_categories')
                ->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('text');
            $table->boolean('pinned')->default(false);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('edited_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wiki_pages');
        Schema::dropIfExists('wiki_categories');
    }
};
