<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Longest description part of a slug, same as Protocol::SLUG_DESCRIPTION_LENGTH when this was written.
     */
    private const SLUG_DESCRIPTION_LENGTH = 40;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('protocols', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
        });

        // Same format as Protocol::generateSlug(), inlined so the migration does not change with the model
        $taken = [];
        foreach (DB::table('protocols')->orderBy('date')->orderBy('id')->get() as $row) {
            $base = collect([substr($row->date, 0, 10), $this->descriptionSlug($row->description)])
                ->filter()
                ->implode('-');
            $slug = $base;
            for ($suffix = 2; isset($taken[$slug]); $suffix++) {
                $slug = "{$base}-{$suffix}";
            }
            $taken[$slug] = true;

            DB::table('protocols')->where('id', $row->id)->update(['slug' => $slug]);
        }

        Schema::table('protocols', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * The description as slug, cut at a word boundary.
     */
    private function descriptionSlug(?string $description): string
    {
        $slug = Str::slug($description ?? '', '-', 'de');

        if (strlen($slug) <= self::SLUG_DESCRIPTION_LENGTH) {
            return $slug;
        }

        $cut = substr($slug, 0, self::SLUG_DESCRIPTION_LENGTH + 1);
        $lastSeparator = strrpos($cut, '-');

        return $lastSeparator ? substr($cut, 0, $lastSeparator) : substr($slug, 0, self::SLUG_DESCRIPTION_LENGTH);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocols', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
