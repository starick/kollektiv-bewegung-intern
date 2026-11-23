<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Replaces the auto-increment protocol ids with ULIDs.
 *
 * The table is rebuilt instead of altered, because swapping a primary key in place
 * works differently on MySQL and SQLite. Existing protocols get a ULID based on their
 * creation time, so they keep sorting in creation order, and their audits are moved
 * over to the new ids.
 */
return new class extends Migration
{
    private const AUDITABLE_TYPE = 'App\\Models\\Protocol';

    public function up(): void
    {
        $this->auditsTable(fn (Blueprint $table) => $table->string('auditable_id', 36)->change());

        $this->rebuild(fn (Blueprint $table) => $table->ulid('id')->primary(), function ($rows) {
            return $rows->mapWithKeys(fn ($row) => [
                $row->id => (string) Str::ulid($row->created_at ? Carbon::parse($row->created_at) : null),
            ]);
        });
    }

    public function down(): void
    {
        $this->rebuild(fn (Blueprint $table) => $table->id(), function ($rows) {
            // ULIDs sort by creation time, so numbering them in order restores the old sequence
            return $rows->sortBy('id')->values()->mapWithKeys(fn ($row, $index) => [$row->id => $index + 1]);
        });

        $this->auditsTable(fn (Blueprint $table) => $table->unsignedBigInteger('auditable_id')->change());
    }

    /**
     * Recreates the protocols table with a new primary key and copies every row over.
     *
     * @param  callable(Blueprint): mixed  $primaryKey
     * @param  callable(\Illuminate\Support\Collection<int, object>): \Illuminate\Support\Collection<mixed, mixed>  $mapIds  old id => new id
     */
    private function rebuild(callable $primaryKey, callable $mapIds): void
    {
        // Foreign key and index names must be free before the new table can claim them
        Schema::table('protocols', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropForeign(['edited_by']);
            $table->dropUnique(['date', 'description']);
        });
        Schema::rename('protocols', 'protocols_old');

        Schema::create('protocols', function (Blueprint $table) use ($primaryKey) {
            $primaryKey($table);
            $table->date('date');
            $table->string('description')->nullable();
            $table->json('topics')->nullable();
            $table->json('participants')->nullable();
            $table->longText('text');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['date', 'description']);
        });

        $rows = DB::table('protocols_old')->orderBy('id')->get();
        $ids = $mapIds($rows);

        DB::transaction(function () use ($rows, $ids) {
            foreach ($rows as $row) {
                DB::table('protocols')->insert(array_merge((array) $row, ['id' => $ids[$row->id]]));

                DB::connection($this->auditsConnection())->table($this->auditsTableName())
                    ->where('auditable_type', self::AUDITABLE_TYPE)
                    ->where('auditable_id', (string) $row->id)
                    ->update(['auditable_id' => (string) $ids[$row->id]]);
            }
        });

        Schema::drop('protocols_old');
    }

    private function auditsTable(callable $callback): void
    {
        Schema::connection($this->auditsConnection())->table($this->auditsTableName(), $callback);
    }

    private function auditsConnection(): ?string
    {
        return config('audit.drivers.database.connection', config('database.default'));
    }

    private function auditsTableName(): string
    {
        return config('audit.drivers.database.table', 'audits');
    }
};
