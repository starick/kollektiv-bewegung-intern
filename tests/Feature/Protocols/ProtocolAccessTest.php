<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProtocolAccessTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_users_outside_the_shared_team_cannot_access_protocols(): void
    {
        $this->sharedTeam();
        $outsider = User::factory()->withPersonalTeam()->create();
        $protocol = Protocol::factory()->create();

        $this->actingAs($outsider);
        $this->get(route('protocols.index'))->assertForbidden();
        $this->get(route('protocols.create'))->assertForbidden();
        $this->get(route('protocols.show', $protocol))->assertForbidden();
        $this->get(route('protocols.download', $protocol))->assertForbidden();
        $this->delete(route('protocols.destroy', $protocol))->assertForbidden();
        $this->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'file' => UploadedFile::fake()->createWithContent('plenum.md', '# Plenum'),
        ])->assertForbidden();

        $this->assertModelExists($protocol);
        $this->assertDatabaseCount('protocols', 1);
    }

    public function test_removed_members_lose_access(): void
    {
        $member = $this->teamMember();
        $this->actingAs($member)->get(route('protocols.index'))->assertOk();

        $this->sharedTeam()->removeUser($member);

        $this->actingAs($member->fresh())->get(route('protocols.index'))->assertForbidden();
    }

    public function test_owner_can_access_protocols(): void
    {
        $this->actingAs($this->sharedTeam()->owner)
            ->get(route('protocols.index'))
            ->assertOk();
    }
}
