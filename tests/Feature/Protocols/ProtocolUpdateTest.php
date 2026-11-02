<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolUpdateTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return [
            'date' => '2026-11-03',
            'description' => 'Plenum Dezember',
            'topics' => 'Budget, Events',
            'text' => "# Plenum\n\n- Punkt 2",
            ...$overrides,
        ];
    }

    public function test_edit_page_is_rendered_with_protocol_text(): void
    {
        $protocol = Protocol::factory()->create(['text' => '# Original']);

        $this->actingAs($this->teamMember())
            ->get(route('protocols.edit', $protocol))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Protocols/Edit')
                ->where('protocol.id', $protocol->id)
                ->where('text', '# Original'));
    }

    public function test_guests_cannot_update_protocols(): void
    {
        $protocol = Protocol::factory()->create();

        $this->get(route('protocols.edit', $protocol))->assertRedirect(route('login'));
        $this->put(route('protocols.update', $protocol), $this->payload())->assertRedirect(route('login'));
    }

    public function test_users_outside_the_shared_team_cannot_update(): void
    {
        $this->sharedTeam();
        $protocol = Protocol::factory()->create(['text' => '# Original']);
        $outsider = User::factory()->withPersonalTeam()->create();

        $this->actingAs($outsider)->get(route('protocols.edit', $protocol))->assertForbidden();
        $this->actingAs($outsider)->put(route('protocols.update', $protocol), $this->payload())->assertForbidden();

        $this->assertSame('# Original', $protocol->fresh()->text);
    }

    public function test_any_team_member_can_update_a_protocol(): void
    {
        $protocol = Protocol::factory()->create();
        $editor = $this->teamMember('editor');

        $this->actingAs($editor)
            ->put(route('protocols.update', $protocol), $this->payload([
                'topics' => ' Budget, Events,, Budget ,',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('protocols.show', $protocol));

        $protocol->refresh();
        $this->assertSame('2026-11-03', $protocol->date->toDateString());
        $this->assertSame('Plenum Dezember', $protocol->description);
        $this->assertSame(['Budget', 'Events'], $protocol->topics);
        $this->assertSame("# Plenum\n\n- Punkt 2", $protocol->text);
        $this->assertSame($editor->id, $protocol->edited_by);
    }

    public function test_uploader_is_kept_when_someone_else_edits(): void
    {
        $uploader = $this->teamMember();
        $protocol = Protocol::factory()->create(['uploaded_by' => $uploader->id]);

        $this->actingAs($this->teamMember())->put(route('protocols.update', $protocol), $this->payload());

        $this->assertSame($uploader->id, $protocol->fresh()->uploaded_by);
    }

    public function test_description_and_topics_can_be_cleared(): void
    {
        $protocol = Protocol::factory()->create(['description' => 'Plenum', 'topics' => ['Budget']]);

        $this->actingAs($this->teamMember())
            ->put(route('protocols.update', $protocol), $this->payload(['description' => '', 'topics' => '']))
            ->assertSessionHasNoErrors();

        $protocol->refresh();
        $this->assertNull($protocol->description);
        $this->assertSame([], $protocol->topics);
    }

    public function test_date_and_text_are_required(): void
    {
        $protocol = Protocol::factory()->create();

        $this->actingAs($this->teamMember())
            ->put(route('protocols.update', $protocol), $this->payload(['date' => '', 'text' => '']))
            ->assertSessionHasErrors(['date', 'text']);
    }

    public function test_protocol_can_keep_its_own_date_and_description(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum']);

        $this->actingAs($this->teamMember())
            ->put(route('protocols.update', $protocol), $this->payload(['date' => '2026-11-02', 'description' => 'Plenum']))
            ->assertSessionHasNoErrors();
    }

    public function test_date_and_description_of_another_protocol_are_rejected(): void
    {
        Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum']);
        $protocol = Protocol::factory()->create(['date' => '2026-11-03', 'description' => 'Plenum']);

        $this->actingAs($this->teamMember())
            ->put(route('protocols.update', $protocol), $this->payload(['date' => '2026-11-02', 'description' => 'Plenum']))
            ->assertSessionHasErrors('date');

        $this->assertSame('2026-11-03', $protocol->fresh()->date->toDateString());
    }

    public function test_show_page_tells_whether_user_can_update(): void
    {
        $protocol = Protocol::factory()->create();

        $this->actingAs($this->teamMember())
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page->where('can.update', true));
    }
}
