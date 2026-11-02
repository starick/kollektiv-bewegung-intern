<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $owner->id, 'personal_team' => false]);
    }

    private function member(string $role): User
    {
        $user = User::factory()->create();
        $this->team->users()->attach($user, ['role' => $role]);
        $user->forceFill(['current_team_id' => $this->team->id])->save();

        return $user;
    }

    public function test_uploader_can_delete_own_protocol(): void
    {
        $uploader = $this->member('editor');
        $protocol = Protocol::factory()->create(['uploaded_by' => $uploader->id]);

        $this->actingAs($uploader)
            ->delete(route('protocols.destroy', $protocol))
            ->assertRedirect(route('protocols.index'));

        $this->assertModelMissing($protocol);
    }

    public function test_editor_cannot_delete_protocol_of_someone_else(): void
    {
        $protocol = Protocol::factory()->create();

        $this->actingAs($this->member('editor'))
            ->delete(route('protocols.destroy', $protocol))
            ->assertForbidden();

        $this->assertModelExists($protocol);
    }

    public function test_administrator_can_delete_any_protocol(): void
    {
        $protocol = Protocol::factory()->create();

        $this->actingAs($this->member('admin'))
            ->delete(route('protocols.destroy', $protocol))
            ->assertRedirect(route('protocols.index'));

        $this->assertModelMissing($protocol);
    }

    public function test_team_owner_can_delete_any_protocol(): void
    {
        $protocol = Protocol::factory()->create();

        $this->actingAs($this->team->owner)
            ->delete(route('protocols.destroy', $protocol))
            ->assertRedirect(route('protocols.index'));

        $this->assertModelMissing($protocol);
    }

    public function test_show_page_tells_whether_user_can_delete(): void
    {
        $uploader = $this->member('editor');
        $protocol = Protocol::factory()->create(['uploaded_by' => $uploader->id]);

        $this->actingAs($uploader)
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page->where('can.delete', true));

        $this->actingAs($this->member('editor'))
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page->where('can.delete', false));
    }
}
