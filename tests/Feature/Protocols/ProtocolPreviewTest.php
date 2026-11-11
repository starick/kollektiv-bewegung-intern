<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolPreviewTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_markdown_is_rendered_without_saving(): void
    {
        $protocol = Protocol::factory()->create(['text' => '# Original']);

        $this->actingAs($this->teamMember())
            ->postJson(route('protocols.preview'), ['text' => "## Agenda\n\n- **Budget**"])
            ->assertOk()
            ->assertJsonPath('html', "<h2>Agenda</h2>\n<ul>\n<li><strong>Budget</strong></li>\n</ul>\n");

        $this->assertSame('# Original', $protocol->fresh()->text);
    }

    public function test_preview_is_sanitised_like_the_protocol_page(): void
    {
        $html = $this->actingAs($this->teamMember())
            ->postJson(route('protocols.preview'), [
                'text' => "<script>alert(1)</script>\n\n[x](javascript:alert(1)) ![img](https://example.com/a.png)",
            ])
            ->assertOk()
            ->json('html');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<img', $html);
    }

    public function test_empty_text_renders_nothing(): void
    {
        $this->actingAs($this->teamMember())
            ->postJson(route('protocols.preview'), ['text' => ''])
            ->assertOk()
            ->assertJsonPath('html', '');
    }

    public function test_text_is_required(): void
    {
        $this->actingAs($this->teamMember())
            ->postJson(route('protocols.preview'), [])
            ->assertJsonValidationErrors('text');
    }

    public function test_guests_cannot_preview(): void
    {
        $this->postJson(route('protocols.preview'), ['text' => '# x'])->assertUnauthorized();
    }

    public function test_users_outside_the_shared_team_cannot_preview(): void
    {
        $this->sharedTeam();

        $this->actingAs(User::factory()->withPersonalTeam()->create())
            ->postJson(route('protocols.preview'), ['text' => '# x'])
            ->assertForbidden();
    }

    public function test_edit_page_includes_uploader_and_editor(): void
    {
        $protocol = Protocol::factory()->edited()->create();

        $this->actingAs($this->teamMember())
            ->get(route('protocols.edit', $protocol))
            ->assertInertia(fn (Assert $page) => $page
                ->where('protocol.uploader.id', $protocol->uploaded_by)
                ->where('protocol.editor.id', $protocol->edited_by)
                ->missing('protocol.editor.email'));
    }
}
