<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

class ProtocolUlidMigrationTest extends TestCase
{
    use RefreshDatabase;

    private Migration $migration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migration = require database_path('migrations/2026_11_23_120000_switch_protocols_to_ulid_ids.php');
    }

    public function test_existing_protocols_and_their_audits_move_to_ulids(): void
    {
        $this->migration->down();

        $user = User::factory()->create();
        $legacy = fn (int $id, string $createdAt) => [
            'id' => $id,
            'date' => "2026-0{$id}-01",
            'description' => "Plenum {$id}",
            'topics' => json_encode(['Kasse']),
            'participants' => json_encode(['Anna']),
            'text' => "# Protokoll {$id}",
            'uploaded_by' => $user->id,
            'edited_by' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
        DB::table('protocols')->insert([
            $legacy(1, '2026-01-01 10:00:00'),
            $legacy(2, '2026-02-01 10:00:00'),
        ]);
        foreach ([1, 2] as $id) {
            DB::table('audits')->insert([
                'event' => 'created',
                'auditable_type' => Protocol::class,
                'auditable_id' => $id,
                'user_type' => User::class,
                'user_id' => $user->id,
                'new_values' => json_encode(['text' => "# Protokoll {$id}"]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->migration->up();

        $protocols = Protocol::orderBy('id')->get();
        $this->assertCount(2, $protocols);
        $this->assertTrue($protocols->every(fn (Protocol $protocol) => Str::isUlid($protocol->id)));
        // Ids follow the creation time, so the old order is kept
        $this->assertSame(['Plenum 1', 'Plenum 2'], $protocols->pluck('description')->all());
        $this->assertSame(['Kasse'], $protocols[0]->topics);
        $this->assertSame($user->id, $protocols[0]->uploaded_by);

        foreach ($protocols as $protocol) {
            $this->assertCount(1, $protocol->audits);
            $this->assertSame($protocol->text, $protocol->audits->first()->new_values['text']);
        }
        $this->assertSame(0, Audit::whereIn('auditable_id', ['1', '2'])->count());
    }

    public function test_new_protocols_get_a_ulid(): void
    {
        $protocol = Protocol::factory()->create();

        $this->assertTrue(Str::isUlid($protocol->id));
        $this->assertSame(1, $protocol->audits()->count());
    }

    public function test_unknown_ids_are_not_found(): void
    {
        $this->actingAs(User::factory()->withPersonalTeam()->create())
            ->get('/protocols/1')
            ->assertNotFound();
    }
}
