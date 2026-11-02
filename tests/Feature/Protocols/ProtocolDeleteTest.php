<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolDeleteTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_uploader_can_delete_own_protocol(): void
    {
        $uploader = $this->teamMember('editor');
        $protocol = Protocol::factory()->create(['uploaded_by' => $uploader->id]);

        $this->actingAs($uploader)
            ->delete(route('protocols.destroy', $protocol))
            ->assertRedirect(route('protocols.index'));

        $this->assertModelMissing($protocol);
    }

    public function test_editor_cannot_delete_protocol_of_someone_else(): void
    {
        $protocol = Protocol::factory()->create();

        $this->actingAs($this->teamMember('editor'))
            ->delete(route('protocols.destroy', $protocol))
            ->assertForbidden();

        $this->assertModelExists($protocol);
    }

    public function test_administrator_can_delete_any_protocol(): void
    {
        $protocol = Protocol::factory()->create();

        $this->actingAs($this->teamMember('admin'))
            ->delete(route('protocols.destroy', $protocol))
            ->assertRedirect(route('protocols.index'));

        $this->assertModelMissing($protocol);
    }

    public function test_team_owner_can_delete_any_protocol(): void
    {
        $protocol = Protocol::factory()->create();

        $this->actingAs($this->sharedTeam()->owner)
            ->delete(route('protocols.destroy', $protocol))
            ->assertRedirect(route('protocols.index'));

        $this->assertModelMissing($protocol);
    }

    public function test_uploader_removed_from_team_cannot_delete(): void
    {
        $uploader = $this->teamMember('editor');
        $protocol = Protocol::factory()->create(['uploaded_by' => $uploader->id]);
        $this->sharedTeam()->removeUser($uploader);

        $this->actingAs($uploader->fresh())
            ->delete(route('protocols.destroy', $protocol))
            ->assertForbidden();

        $this->assertModelExists($protocol);
    }

    public function test_show_page_tells_whether_user_can_delete(): void
    {
        $uploader = $this->teamMember('editor');
        $protocol = Protocol::factory()->create(['uploaded_by' => $uploader->id]);

        $this->actingAs($uploader)
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page->where('can.delete', true));

        $this->actingAs($this->teamMember('editor'))
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page->where('can.delete', false));
    }
}
