<?php

namespace Tests\Unit\Models;

use App\Models\Protocol;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_uploader_relation(): void
    {
        $relation = (new Protocol())->uploader();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame(User::class, $relation->getRelated()::class);
        $this->assertSame('uploaded_by', $relation->getForeignKeyName());
    }

    public function test_belongs_to_editor_relation(): void
    {
        $relation = (new Protocol())->editor();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame(User::class, $relation->getRelated()::class);
        $this->assertSame('edited_by', $relation->getForeignKeyName());
    }

    public function test_casts_date_to_carbon(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02']);

        $this->assertInstanceOf(Carbon::class, $protocol->date);
        $this->assertSame('2026-11-02', $protocol->date->toDateString());
    }

    public function test_user_references_are_nulled_when_user_is_deleted(): void
    {
        $protocol = Protocol::factory()->edited()->create();

        $protocol->uploader->delete();
        $protocol->editor->delete();
        $protocol->refresh();

        $this->assertNull($protocol->uploaded_by);
        $this->assertNull($protocol->edited_by);
    }
}
